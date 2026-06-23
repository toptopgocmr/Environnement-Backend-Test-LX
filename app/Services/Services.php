<?php
namespace App\Services;

use App\Models\{Book, Order, Royalty, User};
use Illuminate\Support\Facades\{Http, DB};
use Illuminate\Support\Str;

// ═══════════════════════════════════════════════════════════
// PAYMENT SERVICE (orchestrator)
// ═══════════════════════════════════════════════════════════
class PaymentService
{
    public function __construct(
        protected MtnMomoService $mtn,
        protected AirtelService  $airtel,
    ) {}

    public function initiate(array $data, Book $book, User $user): array
    {
        $order = Order::create([
            'user_id'        => $user->id,
            'book_id'        => $book->id,
            'amount'         => $book->price,
            'currency'       => $book->currency,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'type'           => $data['type'] ?? 'digital',
        ]);

        return match($data['payment_method']) {
            'mtn_momo'    => $this->mtn->initiate($order, $data['phone']),
            'airtel_money'=> $this->airtel->initiate($order, $data['phone']),
            default       => ['success' => false, 'message' => 'Méthode de paiement inconnue.'],
        };
    }

    public function handleCallback(string $method, array $payload): array
    {
        $handler = match($method) {
            'mtn'    => fn() => $this->mtn->handleCallback($payload),
            'airtel' => fn() => $this->airtel->handleCallback($payload),
            default  => null,
        };

        if (!$handler) return ['success' => false];
        return $handler();
    }

    public static function createRoyalty(Order $order): void
    {
        $rate         = 0.80; // author gets 80%
        $platformFee  = $order->amount * 0.20;
        $netAmount    = $order->amount * $rate;

        Royalty::create([
            'author_id'    => $order->book->author_id,
            'order_id'     => $order->id,
            'gross_amount' => $order->amount,
            'platform_fee' => $platformFee,
            'net_amount'   => $netAmount,
            'currency'     => $order->currency,
            'status'       => 'pending',
        ]);

        // Generate download token
        $order->update([
            'payment_status' => 'paid',
            'download_token' => Str::random(64),
            'expires_at'     => now()->addYears(10),
        ]);
    }
}

// ═══════════════════════════════════════════════════════════
// MTN MOBILE MONEY SERVICE
// ═══════════════════════════════════════════════════════════
class MtnMomoService
{
    private string $baseUrl;
    private string $subscriptionKey;
    private string $apiUser;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl         = config('services.mtn_momo.base_url', 'https://sandbox.momodeveloper.mtn.com');
        $this->subscriptionKey = config('services.mtn_momo.subscription_key');
        $this->apiUser         = config('services.mtn_momo.api_user');
        $this->apiKey          = config('services.mtn_momo.api_key');
    }

    private function getToken(): string
    {
        $response = Http::withBasicAuth($this->apiUser, $this->apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->post("{$this->baseUrl}/collection/token/");

        return $response->json('access_token');
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

// ═══════════════════════════════════════════════════════════
// AIRTEL MONEY SERVICE
// ═══════════════════════════════════════════════════════════
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
}

// ═══════════════════════════════════════════════════════════
// SMS SERVICE (via Africa's Talking or similar)
// ═══════════════════════════════════════════════════════════
class SmsService
{
    public function send(string $phone, string $message): bool
    {
        try {
            $res = Http::withBasicAuth(config('services.sms.username'), config('services.sms.api_key'))
                ->post(config('services.sms.url', 'https://api.africastalking.com/version1/messaging'), [
                    'username' => config('services.sms.username'),
                    'to'       => $phone,
                    'message'  => $message,
                    'from'     => 'LireX',
                ]);
            return $res->successful();
        } catch (\Exception $e) {
            \Log::error('SMS error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        return $this->send($phone, "LireX: Votre code de vérification est {$otp}. Valable 10 minutes.");
    }

    public function sendBookApproved(string $phone, string $bookTitle): bool
    {
        return $this->send($phone, "LireX: Votre livre « {$bookTitle} » vient d'être approuvé et est maintenant en ligne !");
    }
}
