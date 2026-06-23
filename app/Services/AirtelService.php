<?php

namespace App\Services;

use App\Models\{Book, Order, Royalty, User, AuthorPlan};
use Illuminate\Support\Facades\{Http, DB, Log};
use Illuminate\Support\Str;

class AirtelService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl      = config('services.airtel.base_url', 'https://openapi.airtel.africa');
        $this->clientId     = config('services.airtel.client_id');
        $this->clientSecret = config('services.airtel.client_secret');
    }

    private function authenticate(): void
    {
        $res = Http::post("{$this->baseUrl}/auth/oauth2/token", [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials',
        ]);
        $this->accessToken = $res->json('access_token');
    }

    public function initiate(Order $order, string $phone): array
    {
        try {
            $this->authenticate();
            $transactionId = 'LRX' . strtoupper(Str::random(10));

            $res = Http::withToken($this->accessToken)
                ->withHeaders(['X-Country' => 'CG', 'X-Currency' => 'XAF', 'Accept' => 'application/json'])
                ->post("{$this->baseUrl}/merchant/v1/payments/", [
                    'reference'   => $order->reference,
                    'subscriber'  => ['country' => 'CG', 'currency' => 'XAF', 'msisdn' => ltrim($phone, '+')],
                    'transaction' => ['amount' => $order->amount, 'country' => 'CG', 'currency' => 'XAF', 'id' => $transactionId],
                ]);

            $data = $res->json();
            if (($data['status']['success'] ?? false)) {
                $order->update(['transaction_id' => $transactionId]);
                return ['success' => true, 'message' => 'Confirmez le paiement Airtel Money sur votre mobile.', 'transaction_id' => $transactionId];
            }

            return ['success' => false, 'message' => $data['status']['message'] ?? 'Erreur Airtel.'];
        } catch (\Exception $e) {
            \Log::error('Airtel error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion Airtel.'];
        }
    }

    public function handleCallback(array $payload): array
    {
        $transactionId = $payload['transaction']['id']   ?? '';
        $status        = $payload['transaction']['status'] ?? '';

        $order = Order::where('transaction_id', $transactionId)->first();
        if (!$order) return ['success' => false];

        if ($status === 'TS') { // TS = Transaction Successful
            DB::transaction(fn() => PaymentService::createRoyalty($order));
            return ['success' => true];
        }

        $order->update(['payment_status' => 'failed']);
        return ['success' => false];
    }

    /** ── Push payment pour un abonnement forfait ── */
    public function initiatePlan(AuthorPlan $authorPlan, string $phone): array
    {
        try {
            $this->authenticate();
            $phone         = preg_replace('/\D/', '', $phone);
            $transactionId = 'PLAN' . strtoupper(Str::random(12));
            $country       = config('services.airtel.country', 'CG');
            $currency      = config('services.airtel.currency', 'XAF');

            $res = Http::withToken($this->accessToken)
                ->withHeaders([
                    'X-Country'  => $country,
                    'X-Currency' => $currency,
                    'Accept'     => 'application/json',
                ])
                ->post("{$this->baseUrl}/merchant/v1/payments/", [
                    'reference'   => "LireX-Forfait-{$authorPlan->id}",
                    'subscriber'  => ['country' => $country, 'currency' => $currency, 'msisdn' => $phone],
                    'transaction' => [
                        'amount'   => intval($authorPlan->amount_paid),
                        'country'  => $country,
                        'currency' => $currency,
                        'id'       => $transactionId,
                    ],
                ]);

            $data = $res->json();

            if ($data['status']['success'] ?? false) {
                $authorPlan->update(['transaction_id' => $transactionId]);
                return [
                    'success'        => true,
                    'transaction_id' => $transactionId,
                    'message'        => 'Confirmez le paiement Airtel Money sur votre mobile.',
                ];
            }

            Log::error('Airtel plan initiate failed', ['data' => $data]);
            return ['success' => false, 'message' => $data['status']['message'] ?? 'Erreur Airtel Money. Vérifiez votre numéro.'];
        } catch (\Exception $e) {
            Log::error('Airtel plan exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion Airtel. Réessayez dans quelques instants.'];
        }
    }

    /** ── Vérifier le statut d'un paiement forfait ── */
    public function checkPlanStatus(AuthorPlan $authorPlan): array
    {
        if (!$authorPlan->transaction_id) {
            return ['status' => 'PENDING'];
        }

        try {
            $this->authenticate();
            $country  = config('services.airtel.country', 'CG');
            $currency = config('services.airtel.currency', 'XAF');

            $res  = Http::withToken($this->accessToken)
                ->withHeaders(['X-Country' => $country, 'X-Currency' => $currency, 'Accept' => 'application/json'])
                ->get("{$this->baseUrl}/standard/v1/payments/{$authorPlan->transaction_id}");

            $data   = $res->json();
            $status = $data['data']['transaction']['status'] ?? 'PENDING';

            // Airtel statuses: TS = success, TF = failed, PENDING
            if ($status === 'TS') {
                $this->activatePlan($authorPlan);
                return ['status' => 'SUCCESSFUL'];
            } elseif ($status === 'TF') {
                $authorPlan->update(['status' => 'payment_failed']);
                return ['status' => 'FAILED'];
            }

            return ['status' => 'PENDING'];
        } catch (\Exception $e) {
            Log::error('Airtel check plan status: ' . $e->getMessage());
            return ['status' => 'PENDING'];
        }
    }

    private function activatePlan(AuthorPlan $authorPlan): void
    {
        DB::transaction(function () use ($authorPlan) {
            AuthorPlan::where('user_id', $authorPlan->user_id)
                ->where('status', 'active')
                ->where('id', '!=', $authorPlan->id)
                ->update(['status' => 'superseded']);

            $authorPlan->update(['status' => 'active']);
        });
    }
}
