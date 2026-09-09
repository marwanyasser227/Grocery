<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Services\OrderService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, OrderService $orderService): JsonResponse
    {
        $tracked = $orderService->trackOrder($request->user());

        if (! $tracked) {
            return $this->errorResponse('No active order found', null, 404);
        }

        if ($tracked['awaiting_payment']) {
            return $this->successResponse(
                'Order is waiting for payment. Complete checkout to continue.',
                [
                    'order' => new OrderResource($tracked['order']),
                    'awaiting_payment' => true,
                    'tracking' => null,
                ]
            );
        }

        return $this->successResponse(
            'Order tracking retrieved successfully',
            [
                'order' => new OrderResource($tracked['order']),
                'tracking' => $tracked['tracking'],
            ]
        );
    }
}
