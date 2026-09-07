<?php

namespace App\Actions\Stripe;

use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class ProcessStripeWebhookAction
{
    public function __construct(
        private readonly StripeWebhookService $webhookService
    ) {}
    public function execute(string $payload, ?string $signature): void
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            throw new \RuntimeException('Webhook secret not configured.');
        }
        $event = Webhook::constructEvent($payload, $signature ?? '', $secret);
        $this->webhookService->handleEvent($event);
    }
}
