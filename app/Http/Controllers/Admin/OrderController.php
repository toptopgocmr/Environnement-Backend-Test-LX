<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
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
}
