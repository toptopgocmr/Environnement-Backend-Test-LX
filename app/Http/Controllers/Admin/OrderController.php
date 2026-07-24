<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use App\Services\{PaymentService, PeexService};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{DB, Storage};

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'book.author'])
            ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
            ->when($request->method, fn($q) => $q->where('payment_method', $request->method))
            ->latest()->paginate(20);

        $summary = [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('amount'),
            'today_revenue' => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('amount'),
            'pending_count' => Order::where('payment_status', 'pending')->count(),
        ];
        return view('admin.orders.index', compact('orders', 'summary'));
    }

    /**
     * Interroge Peex pour connaître le statut réel d'une commande en attente
     * et met à jour la commande en conséquence (bouton "Actualiser" admin).
     */
    public function refreshStatus(Order $order): JsonResponse
    {
        if ($order->payment_method !== 'peex' || !$order->transaction_id) {
            return response()->json([
                'success' => false,
                'message' => "Le statut de cette commande ne peut pas être vérifié automatiquement.",
            ], 422);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'success' => true,
                'status'  => $order->payment_status,
                'message' => 'Cette commande n\'est plus en attente.',
            ]);
        }

        $result = app(PeexService::class)->checkStatus($order->transaction_id);
        $status = $result['status'] ?? null;

        if ($status === 'paid') {
            DB::transaction(fn () => PaymentService::createRoyalty($order));
            return response()->json(['success' => true, 'status' => 'paid', 'message' => 'Paiement confirmé.']);
        }

        if (in_array($status, ['failed', 'canceled', 'rejected'], true)) {
            $order->update(['payment_status' => 'failed']);
            return response()->json(['success' => true, 'status' => 'failed', 'message' => 'Paiement échoué ou annulé.']);
        }

        return response()->json(['success' => true, 'status' => 'pending', 'message' => 'Toujours en attente de confirmation.']);
    }
}
