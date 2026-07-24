<?php

namespace App\Services;

use App\Models\{Book, Order, Royalty, User};
use Illuminate\Support\Facades\{Http, DB};
use Illuminate\Support\Str;

// PAYMENT SERVICE (orchestrator)
class PaymentService
{
    public function __construct(
        protected PeexService $peex,
    ) {}

    public function initiate(array $data, Book $book, User $user): array
    {
        // Réutilise une commande déjà en attente pour ce livre plutôt que d'en
        // recréer une à chaque clic sur "Payer" (retry, changement d'opérateur…).
        $order = Order::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('payment_status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->latest()
            ->first();

        if ($order) {
            $order->update(['payment_method' => $data['payment_method']]);
        } else {
            $order = Order::create([
                'user_id'        => $user->id,
                'book_id'        => $book->id,
                'amount'         => $book->price,
                'currency'       => $book->currency,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'type'           => $data['type'] ?? 'digital',
            ]);
        }

        return match($data['payment_method']) {
            'peex'  => $this->peex->initiate($order, $data['phone'], $user->name, $data['country'] ?? 'CG'),
            default => ['success' => false, 'message' => 'Méthode de paiement inconnue.'],
        };
    }

    public function handleCallback(string $method, array $payload): array
    {
        $handler = match($method) {
            'peex'  => fn() => $this->peex->handleCallback($payload),
            default => null,
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
