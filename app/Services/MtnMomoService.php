<?php

namespace App\Services;

use App\Models\{Book, Order, Royalty, User, AuthorPlan};
use Illuminate\Support\Facades\{Http, DB, Log};
use Illuminate\Support\Str;

class MtnMomoService
{
    private string $baseUrl;
    private string $subscriptionKey;
    private string $apiUser;
    private string $apiKey;
    private string $env;
    private string $currency;

    public function __construct()
    {
        $this->baseUrl         = config('services.mtn_momo.base_url', 'https://sandbox.momodeveloper.mtn.com');
        $this->subscriptionKey = config('services.mtn_momo.subscription_key');
        $this->apiUser         = config('services.mtn_momo.api_user');
        $this->apiKey          = config('services.mtn_momo.api_key');
        $this->env             = config('services.mtn_momo.environment', 'sandbox');
        $this->currency        = config('services.mtn_momo.currency', 'EUR');
    }

    private function getToken(): string
    {
        $response = Http::withBasicAuth($this->apiUser, $this->apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->post("{$this->baseUrl}/collection/token/");

        return $response->json('access_token');
    }

    private function headers(string $referenceId): array
    {
        return [
            'X-Reference-Id'            => $referenceId,
            'X-Target-Environment'      => $this->env,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'Content-Type'              => 'application/json',
        ];
    }

    /** ── Push payment pour un abonnement forfait ── */
    public function initiatePlan(AuthorPlan $authorPlan, string $phone): array
    {
        try {
            $token      = $this->getToken();
            $referenceId = Str::uuid()->toString();
            $phone       = preg_replace('/\D/', '', $phone); // garder seulement les chiffres

            $response = Http::withToken($token)
                ->withHeaders($this->headers($referenceId))
                ->post("{$this->baseUrl}/collection/v1_0/requesttopay", [
                    'amount'       => (string) intval($authorPlan->amount_paid),
                    'currency'     => $this->currency,
                    'externalId'   => 'PLAN-' . $authorPlan->id,
                    'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                    'payerMessage' => "Abonnement LireX — {$authorPlan->plan->name}",
                    'payeeNote'    => "Forfait {$authorPlan->plan->name} ({$authorPlan->billing})",
                ]);

            if ($response->status() === 202) {
                $authorPlan->update(['transaction_id' => $referenceId]);
                return [
                    'success'      => true,
                    'reference_id' => $referenceId,
                    'message'      => 'Demande envoyée. Confirmez le paiement sur votre téléphone MTN.',
                ];
            }

            Log::error('MTN MoMo plan initiate failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'message' => 'Échec MTN MoMo (' . $response->status() . '). Vérifiez votre numéro et réessayez.'];
        } catch (\Exception $e) {
            Log::error('MTN MoMo plan exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion avec MTN. Réessayez dans quelques instants.'];
        }
    }

    /** ── Vérifier le statut d'un paiement forfait ── */
    public function checkPlanStatus(AuthorPlan $authorPlan): array
    {
        if (!$authorPlan->transaction_id) {
            return ['status' => 'PENDING', 'reason' => 'No reference'];
        }

        try {
            $token = $this->getToken();
            $res   = Http::withToken($token)
                ->withHeaders([
                    'X-Target-Environment'      => $this->env,
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                ])
                ->get("{$this->baseUrl}/collection/v1_0/requesttopay/{$authorPlan->transaction_id}");

            $data   = $res->json();
            $status = $data['status'] ?? 'PENDING';

            if ($status === 'SUCCESSFUL') {
                $this->activatePlan($authorPlan);
            } elseif ($status === 'FAILED') {
                $authorPlan->update(['status' => 'payment_failed']);
            }

            return ['status' => $status, 'reason' => $data['reason'] ?? null];
        } catch (\Exception $e) {
            Log::error('MTN check plan status: ' . $e->getMessage());
            return ['status' => 'PENDING', 'reason' => 'check_error'];
        }
    }

    private function activatePlan(AuthorPlan $authorPlan): void
    {
        DB::transaction(function () use ($authorPlan) {
            // Désactiver les anciens abonnements actifs
            AuthorPlan::where('user_id', $authorPlan->user_id)
                ->where('status', 'active')
                ->where('id', '!=', $authorPlan->id)
                ->update(['status' => 'superseded']);

            $authorPlan->update(['status' => 'active']);
        });
    }

    public function initiate(Order $order, string $phone): array
    {
        try {
            $token     = $this->getToken();
            $externalId = Str::uuid()->toString();

            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Reference-Id'             => $externalId,
                    'X-Target-Environment'       => 'sandbox',
                    'Ocp-Apim-Subscription-Key'  => $this->subscriptionKey,
                ])
                ->post("{$this->baseUrl}/collection/v1_0/requesttopay", [
                    'amount'       => (string) $order->amount,
                    'currency'     => 'EUR', // sandbox uses EUR
                    'externalId'   => $externalId,
                    'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => ltrim($phone, '+')],
                    'payerMessage' => "Achat livre LireX – {$order->reference}",
                    'payeeNote'    => "Commande {$order->reference}",
                ]);

            if ($response->status() === 202) {
                $order->update(['transaction_id' => $externalId]);
                return [
                    'success'     => true,
                    'message'     => 'Demande envoyée. Validez le paiement sur votre téléphone.',
                    'external_id' => $externalId,
                    'order_id'    => $order->id,
                ];
            }

            return ['success' => false, 'message' => 'Échec MTN MoMo: ' . $response->body()];
        } catch (\Exception $e) {
            \Log::error('MTN MoMo error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion MTN.'];
        }
    }

    public function checkStatus(string $externalId): array
    {
        $token = $this->getToken();
        $res   = Http::withToken($token)
            ->withHeaders(['X-Target-Environment' => 'sandbox', 'Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->get("{$this->baseUrl}/collection/v1_0/requesttopay/{$externalId}");

        return $res->json();
    }

    public function handleCallback(array $payload): array
    {
        $status     = $payload['status'] ?? '';
        $externalId = $payload['externalId'] ?? '';

        $order = Order::where('transaction_id', $externalId)->first();
        if (!$order) return ['success' => false, 'message' => 'Commande introuvable.'];

        if ($status === 'SUCCESSFUL') {
            DB::transaction(function () use ($order) {
                PaymentService::createRoyalty($order);
            });
            return ['success' => true, 'message' => 'Paiement confirmé.'];
        }

        $order->update(['payment_status' => 'failed']);
        return ['success' => false, 'message' => 'Paiement échoué ou annulé.'];
    }
}
