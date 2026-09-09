<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\SmartListService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SmartListService $smartListService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $smartLists = $this->smartListService->getUserSmartLists($request->user());

        return $this->successResponse(
            'Smart lists retrieved successfully',
            SmartListResource::collection($smartLists)
        );
    }

    public function store(SmartListRequest $request): JsonResponse
    {
        $smartList = $this->smartListService->createSmartList(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            'Wish list created successfully',
            new SmartListResource($smartList),
            201
        );
    }

    public function show(Request $request, SmartList $smart_list): JsonResponse
    {
        $listDetails = $this->smartListService->getSmartListForUser($smart_list, $request->user());

        return $this->successResponse(
            'Smart list retrieved successfully',
            new SmartListResource($listDetails)
        );
    }

    public function update(SmartListRequest $request, SmartList $smart_list): JsonResponse
    {
        $updatedList = $this->smartListService->updateSmartList(
            $smart_list,
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            'Wish list updated successfully',
            new SmartListResource($updatedList)
        );
    }

    public function destroy(Request $request, SmartList $smart_list): JsonResponse
    {
        $this->smartListService->deleteSmartList($smart_list, $request->user());

        return $this->successResponse('Wish list deleted successfully');
    }
}
