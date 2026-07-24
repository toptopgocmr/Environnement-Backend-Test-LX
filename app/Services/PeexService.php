<?php

namespace App\Services;

use App\Models\{Order, AuthorPlan};
use Illuminate\Support\Facades\{Http, DB, Log};
use Illuminate\Support\Str;

/**
 * Intégration Peex (Peers Exchange) — Collect API.
 * Doc : https://peex-api-docs.peexit.com/collect
 *
 * Peex agit comme agrégateur mobile money : une seule intégration pour
 * collecter des paiements MTN / Orange / etc. via /collection/request_payment.
 */
class PeexService
{
    private string $baseUrl;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.peex.base_url', 'https://sandbox.peexit.com/api/v1'), '/');
        $this->secretKey = config('services.peex.secret_key') ?? '';
    }

    /** Vrai si la clé secrète Peex est configurée. */
    public function isConfigured(): bool
    {
        return $this->secretKey !== '';
    }

    private function headers(): array
    {
        return [
            'SECRETKEY'    => $this->secretKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Démarre une collecte de fonds sur le wallet mobile money du client.
     *
     * @param Order  $order        Commande locale (son "reference" sert de track_id unique côté Peex).
     * @param string $phone        Numéro au format international, ex: +237678923563.
     * @param string $customerName Nom complet du client (exigé par Peex pour la conformité).
     * @param string $country      Code pays ISO Alpha-2, ex: "CG", "CM".
     */
    public function initiate(Order $order, string $phone, string $customerName, string $country = 'CG'): array
    {
        if (!$this->isConfigured()) {
            Log::error('Peex non configuré (PEEX_SECRET_KEY manquant).');
            return [
                'success' => false,
                'message' => 'Paiement momentanément indisponible. Veuillez réessayer plus tard ou choisir un autre moyen de paiement.',
            ];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/collection/request_payment", [
                    'track_id'      => $order->reference,
                    'phone'         => $phone,
                    'amount'        => (float) $order->amount,
                    'currency'      => $order->currency ?: 'XAF',
                    'customer_name' => $customerName,
                    'country'       => strtoupper($country),
                    'description'   => "Achat livre LireX - {$order->reference}",
                ]);

            $data = $response->json() ?? [];

            // Réponse imbriquée { error: { message } } en cas d'échec HTTP 4xx/5xx.
            if (!$response->successful()) {
                $message = $data['error']['message'] ?? $data['message'] ?? 'Échec du paiement Peex.';
                Log::error('Peex initiate failed', ['status' => $response->status(), 'body' => $response->body()]);
                return ['success' => false, 'message' => $message];
            }

            $order->update([
                'transaction_id' => $order->reference,
                'payment_data'   => $data,
            ]);

            return [
                'success'  => true,
                'message'  => 'Demande envoyée. Validez le paiement sur votre téléphone.',
                'track_id' => $order->reference,
                'status'   => $data['status'] ?? 'pending',
                'order_id' => $order->id,
            ];
        } catch (\Exception $e) {
            Log::error('Peex exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion avec le service de paiement.'];
        }
    }

    /**
     * Démarre une collecte pour le paiement d'un forfait auteur (abonnement).
     */
    public function initiatePlan(AuthorPlan $authorPlan, string $phone, string $customerName, string $country = 'CG'): array
    {
        if (!$this->isConfigured()) {
            Log::error('Peex non configuré (PEEX_SECRET_KEY manquant).');
            return [
                'success' => false,
                'message' => 'Paiement momentanément indisponible. Veuillez réessayer plus tard ou choisir un autre moyen de paiement.',
            ];
        }

        try {
            $trackId = 'PLAN-' . $authorPlan->id . '-' . strtoupper(Str::random(6));

            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/collection/request_payment", [
                    'track_id'      => $trackId,
                    'phone'         => $phone,
                    'amount'        => (float) $authorPlan->amount_paid,
                    'currency'      => $authorPlan->currency ?: 'XAF',
                    'customer_name' => $customerName,
                    'country'       => strtoupper($country),
                    'description'   => "Abonnement LireX - forfait #{$authorPlan->plan_id}",
                ]);

            $data = $response->json() ?? [];

            if (!$response->successful()) {
                $message = $data['error']['message'] ?? $data['message'] ?? 'Échec du paiement Peex.';
                Log::error('Peex initiatePlan failed', ['status' => $response->status(), 'body' => $response->body()]);
                return ['success' => false, 'message' => $message];
            }

            $authorPlan->update(['transaction_id' => $trackId]);

            return [
                'success'  => true,
                'message'  => 'Demande envoyée. Confirmez le paiement sur votre téléphone.',
                'track_id' => $trackId,
            ];
        } catch (\Exception $e) {
            Log::error('Peex initiatePlan exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion avec le service de paiement.'];
        }
    }

    /** Vérifie le statut du paiement d'un forfait auteur (polling front-end). */
    public function checkPlanStatus(AuthorPlan $authorPlan): array
    {
        if (!$authorPlan->transaction_id) {
            return ['status' => 'PENDING'];
        }

        $data   = $this->checkStatus($authorPlan->transaction_id);
        $status = $data['status'] ?? 'pending';

        if ($status === 'paid') {
            $this->activatePlan($authorPlan);
            return ['status' => 'SUCCESSFUL'];
        }

        if (in_array($status, ['failed', 'canceled', 'rejected'], true)) {
            $authorPlan->update(['status' => 'payment_failed']);
            return ['status' => 'FAILED'];
        }

        return ['status' => 'PENDING'];
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

    /** Interroge le statut d'une transaction via son track_id. */
    public function checkStatus(string $trackId): array
    {
        if (!$this->isConfigured()) {
            return ['status' => 'pending'];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/collection/all_requests", ['track_id' => $trackId]);

            return $response->json() ?? ['status' => 'pending'];
        } catch (\Exception $e) {
            Log::error('Peex check status: ' . $e->getMessage());
            return ['status' => 'pending'];
        }
    }

    /**
     * Traite le webhook Peex. Le corps envoyé est un TABLEAU de transactions
     * non transmises (voir https://peex-api-docs.peexit.com/notifications).
     */
    public function handleCallback(array $payload): array
    {
        $transactions = array_is_list($payload) ? $payload : [$payload];
        $handled = 0;

        foreach ($transactions as $tx) {
            $trackId = $tx['track_id'] ?? null;
            $status  = $tx['status']   ?? null;
            if (!$trackId) continue;

            // Forfaits auteur : track_id préfixé "PLAN-{id}-..."
            if (str_starts_with($trackId, 'PLAN-')) {
                $authorPlan = AuthorPlan::where('transaction_id', $trackId)->first();
                if (!$authorPlan || $authorPlan->status === 'active') continue;

                if ($status === 'paid') {
                    $this->activatePlan($authorPlan);
                    $handled++;
                } elseif (in_array($status, ['failed', 'canceled', 'rejected'], true)) {
                    $authorPlan->update(['status' => 'payment_failed']);
                    $handled++;
                }
                continue;
            }

            $order = Order::where('reference', $trackId)->first();
            if (!$order || $order->payment_status === 'paid') continue;

            if ($status === 'paid') {
                DB::transaction(function () use ($order, $tx) {
                    $order->update(['payment_data' => $tx]);
                    PaymentService::createRoyalty($order);
                });
                $handled++;
            } elseif (in_array($status, ['failed', 'canceled', 'rejected'], true)) {
                $order->update(['payment_status' => 'failed', 'payment_data' => $tx]);
                $handled++;
            }
            // 'new' / 'pending' : rien à faire, on attend le prochain callback.
        }

        return ['success' => true, 'handled' => $handled];
    }
}
