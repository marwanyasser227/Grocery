<?php

namespace App\Actions\Stripe;

use App\Jobs\SendInvoiceEmailJob;
use App\Models\Order;

class HandleSuccessfulPaymentCallbackAction
{
    public function __construct(
        protected VerifyStripeCheckoutSessionAction $verifyStripeCheckoutSessionAction,
    ) {}

    public function execute(string $sessionId): Order
    {
        $order = $this->verifyStripeCheckoutSessionAction->execute($sessionId);

        SendInvoiceEmailJob::dispatch($order->id);

        return $order;
    }
}
