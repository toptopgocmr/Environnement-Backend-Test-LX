<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Order;
use App\Services\MtnMomoService;
use App\Services\AirtelService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ─── Vérifie les paiements en attente (polling) ───────────────────────────
Schedule::call(function () {
    $pendingOrders = Order::where('payment_status', 'pending')
        ->whereNotNull('transaction_id')
        ->where('created_at', '>=', now()->subHours(2))
        ->get();

    foreach ($pendingOrders as $order) {
        try {
            if ($order->payment_method === 'mtn_momo') {
                $service = app(MtnMomoService::class);
                $result  = $service->checkStatus($order->transaction_id);
                if (($result['status'] ?? '') === 'SUCCESSFUL') {
                    \DB::transaction(fn() => \App\Services\PaymentService::createRoyalty($order));
                }
            } elseif ($order->payment_method === 'airtel_money') {
                $service = app(AirtelService::class);
                $result  = $service->checkStatus($order->transaction_id);
                if (($result['success'] ?? false)) {
                    \DB::transaction(fn() => \App\Services\PaymentService::createRoyalty($order));
                }
            }
        } catch (\Exception $e) {
            \Log::error("Payment polling error order #{$order->id}: " . $e->getMessage());
        }
    }
})->everyFiveMinutes()->name('poll-pending-payments');

// ─── Marque les orders trop anciens comme failed ──────────────────────────
Schedule::call(function () {
    Order::where('payment_status', 'pending')
        ->where('created_at', '<', now()->subHours(4))
        ->update(['payment_status' => 'failed']);
})->hourly()->name('expire-stale-orders');
