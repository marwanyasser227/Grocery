<?php

namespace App\Actions\Stripe;

use App\Models\Order;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Stripe\Checkout\Session;

class CreateStripeCheckoutSessionAction
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    public function execute(Order $order, User $user, float $amount): Session
    {
        $session = $this->checkoutService->createSessionForOrder($order, $user, $amount);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session;
    }
}
