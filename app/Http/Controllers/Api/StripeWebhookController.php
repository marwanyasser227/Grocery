<?php

namespace App\Http\Controllers\Api;

use App\Actions\Stripe\ProcessStripeWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessStripeWebhookAction $action): Response
    {
        $action->execute($request->getContent(), $request->header('Stripe-Signature'));

        return response('OK', 200);
    }
}
