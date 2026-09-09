<?php

namespace App\Http\Controllers\Api;

use App\Actions\Stripe\CreateStripeCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;

class CreateStripeCheckoutSessionController extends Controller
{
    use ApiResponse;

    public function __invoke(
        CreateStripeCheckoutSessionRequest $request,
        CreateStripeCheckoutSessionAction $action
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()
            ->whereKey($data['order_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $session = $action->execute($order, $user, (float) $data['amount']);

        return $this->successResponse('Checkout session created.', [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
            'order_id' => $order->id,
        ]);
    }
}
