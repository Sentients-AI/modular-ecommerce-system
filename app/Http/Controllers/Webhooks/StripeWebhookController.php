<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Jobs\HandleStripeEventJob;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController
{
    /**
     * @throws SignatureVerificationException
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent(
            $payload,
            $sig,
            config('services.stripe.webhook_secret')
        );

        dispatch(new HandleStripeEventJob($event));

        return response()->noContent();
    }
}
