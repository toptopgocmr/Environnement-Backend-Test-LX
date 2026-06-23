<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'         => User::where('role', 'reader')->count(),
            'total_authors'       => User::where('role', 'author')->count(),
            'total_books'         => Book::published()->count(),
            'pending_books'       => Book::pending()->count(),
            'total_orders'        => Order::where('payment_status', 'paid')->count(),
            'total_revenue'       => Order::where('payment_status', 'paid')->sum('amount'),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'new_users_today'     => User::whereDate('created_at', today())->count(),
        ];

        $recentOrders = Order::with(['user', 'book'])
            ->where('payment_status', 'paid')
            ->latest()->limit(10)->get();

        $topBooks = Book::published()
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->orderByDesc('orders_count')
            ->limit(5)->get();

        // MySQL : DATE_FORMAT au lieu de TO_CHAR
        $revenueChart = Order::where('payment_status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)->get();

        $pendingBooks       = Book::pending()->with('author')->latest()->limit(5)->get();
        $pendingWithdrawals = WithdrawalRequest::where('status', 'pending')->with('author')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'topBooks', 'revenueChart', 'pendingBooks', 'pendingWithdrawals'
        ));
    }
}
