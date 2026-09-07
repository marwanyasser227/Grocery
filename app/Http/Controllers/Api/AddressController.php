<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Traits\V1\ApiResponse;
use App\Http\Resources\AddressResource;
use App\Actions\Address\FindUserAddressAction;
use App\Actions\Address\CreateAddressAction;
use App\Actions\Address\UpdateAddressAction;
use App\Actions\Address\DeleteAddressAction;
use App\Actions\Address\SetDefaultAddressAction;

class AddressController extends Controller
{
    use ApiResponse;
    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->allFiles() !== []) {
            return self::errorResponse(
                'This endpoint does not accept file uploads.',
                ['files' => ['Remove file attachments from the request.']],
                422
            );
        }

        $addresses = AddressResource::collection(
            $request->user()->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
        );

        return self::successResponse('Addresses retrieved successfully', [
            'addresses' => $addresses,
            'total_count' => $addresses->count(),
        ], 200);
    }

    /**
     * Get single address
     */
    public function show(Request $request, string $id, FindUserAddressAction $action): JsonResponse
    {
        $address = $action->handle($request->user(), $id);

        return self::successResponse('Address retrieved successfully', new AddressResource($address));
    }

    /**
     * Create new address
     */
    public function store(AddressRequest $request, CreateAddressAction $action): JsonResponse
    {
        $address = $action->handle($request->user(), $request->validated());

        return self::successResponse('Address created successfully', new AddressResource($address), 201);
    }

    /**
     * Update address
     */
    public function update(AddressRequest $request, string $id, FindUserAddressAction $findAction, UpdateAddressAction $updateAction): JsonResponse
    {
        $address = $findAction->handle($request->user(), $id);
        $address = $updateAction->handle($address, $request->validated());

        return self::successResponse('Address updated successfully', new AddressResource($address));
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, string $id, FindUserAddressAction $findAction, DeleteAddressAction $deleteAction): JsonResponse
    {
        $user = $request->user();
        $address = $findAction->handle($user, $id);
        $deleteAction->handle($user, $address);

        return self::successResponse('Address deleted successfully');
    }

    /**
     * Set address as default
     */
    public function setDefault(Request $request, string $id, FindUserAddressAction $findAction, SetDefaultAddressAction $setDefaultAction): JsonResponse
    {
        $user = $request->user();
        $address = $findAction->handle($user, $id);

        if ($address->is_default) {
            return self::successResponse('This address is already your default.', [
                'already_default' => true,
                'address' => new AddressResource($address),
            ]);
        }

        $address = $setDefaultAction->handle($user, $address);

        return self::successResponse('Default address updated successfully', new AddressResource($address));
    }
}
