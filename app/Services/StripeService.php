<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(float $amount, string $currency = 'xaf'): array
    {
        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount'   => (int) ($amount * 100),
                'currency' => strtolower($currency),
                'metadata' => ['platform' => 'lirex'],
            ]);
            return ['success' => true, 'client_secret' => $intent->client_secret, 'intent_id' => $intent->id];
        } catch (\Exception $e) {
            Log::error('Stripe error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function confirmPayment(string $intentId): bool
    {
        try {
            $intent = \Stripe\PaymentIntent::retrieve($intentId);
            return $intent->status === 'succeeded';
        } catch (\Exception $e) {
            return false;
        }
    }
}
