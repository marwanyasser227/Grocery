<?php

namespace App\Http\Controllers;

use App\Actions\Stripe\VerifyStripeCheckoutSessionAction;
use App\Http\Resources\Api\OrderPaymentResource;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StripePaymentCallbackController extends Controller
{
    use ApiResponse;

    public function success(Request $request, VerifyStripeCheckoutSessionAction $action): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            throw new InvalidArgumentException('Missing session_id parameter.');
        }

        $order = $action->execute($sessionId);

        return $this->successResponse(
            'Payment successful. Your order has been placed.',
            new OrderPaymentResource($order)
        );
    }

    public function cancel(Request $request): JsonResponse
    {
        return $this->errorResponse(
            'Payment was cancelled.',
            ['order_id' => $request->query('order_id')],
            200
        );
    }
}
