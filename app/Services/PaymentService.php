<?php

namespace App\Services;

use App\Models\{Book, Order, Royalty, User};
use Illuminate\Support\Facades\{Http, DB};
use Illuminate\Support\Str;

// PAYMENT SERVICE (orchestrator)
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
