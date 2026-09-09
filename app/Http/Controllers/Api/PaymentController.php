<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderReceiptResource;
use App\Http\Resources\Api\PaymentHistoryResource;
use App\Models\Order;
use App\Services\PaymentService;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * Get payment history for the authenticated user.
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        $orders = $this->paymentService->getPaymentHistory($request->user());

        return $this->successResponse(
            'Payment history retrieved successfully',
            PaymentHistoryResource::collection($orders)
        );
    }

    /**
     * Get receipt for a specific order.
     */
    public function receipt(Request $request, Order $order): JsonResponse
    {
        $orderReceipt = $this->paymentService->getReceiptForUser($order, $request->user());

        return $this->successResponse(
            'Receipt retrieved successfully',
            new OrderReceiptResource($orderReceipt)
        );
    }

    /**
     * Get invoice for a specific order (PDF download).
     */
    public function invoice(Request $request, Order $order)
    {
        $pdf = $this->paymentService->generateInvoicePdf($order, $request->user());

        return $pdf->download('invoice.pdf'.$order->id.'.pdf');
    }
}
