<?php

namespace App\Http\Controllers\Api;

use App\Actions\Stripe\VerifyStripeCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderPaymentResource;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyStripeCheckoutSessionController extends Controller
{
    use ApiResponse;

    public function __invoke(
        Request $request,
        string $sessionId,
        VerifyStripeCheckoutSessionAction $action
    ): JsonResponse {
        $order = $action->execute($sessionId, $request->user());

        return $this->successResponse(
            'Payment verified. Order is placed.',
            new OrderPaymentResource($order)
        );
    }
}
