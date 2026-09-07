<?php

namespace App\Actions\Stripe;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class VerifyStripeCheckoutSessionAction
{
    public function execute(string $sessionId, ?User $user = null): Order
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            throw new \DomainException('Payment has not been completed.');
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        if (! $orderId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order metadata missing in session.');
        }

        $query = Order::query()->whereKey((int) $orderId);
        if ($user) {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (! $order) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found.');
        }

        if ($order->status === 'awaiting_payment') {
            $pi = $session->payment_intent;
            $paymentIntentId = is_string($pi) ? $pi : ($pi->id ?? null);

            DB::transaction(function () use ($order, $paymentIntentId, $session) {
                $order->refresh();
                if ($order->status !== 'awaiting_payment') {
                    return;
                }

                $order->update([
                    'status' => 'placed',
                    'placed_at' => now(),
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'stripe_checkout_session_id' => $session->id,
                ]);
            });

            $order->refresh();
        }

        return $order;
    }
}
