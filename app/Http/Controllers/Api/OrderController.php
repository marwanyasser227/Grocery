<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Get all orders for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders($request->user());

        return $this->successResponse(
            'Orders retrieved successfully',
            OrderResource::collection($orders)
        );
    }

    /**
     * Display a specific order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $orderDetails = $this->orderService->getOrderDetails($order, $request->user());

        return $this->successResponse(
            'Order retrieved successfully',
            new OrderResource($orderDetails)
        );
    }

    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->user(), $request->validated());

        return $this->successResponse(
            'Order created successfully',
            new OrderResource($order),
            201
        );
    }
}
