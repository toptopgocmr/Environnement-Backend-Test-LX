<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, Royalty, WithdrawalRequest, BookTag};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, DB};
use Illuminate\Support\Str;

// AUTHOR DASHBOARD  –  MySQL : DATE_FORMAT au lieu de TO_CHAR
class DashboardController extends Controller
{
    public function index()
    {
        $author = Auth::user();

        $stats = [
            'total_books'     => $author->books()->count(),
            'published_books' => $author->books()->where('status', 'published')->count(),
            'pending_books'   => $author->books()->where('status', 'pending')->count(),
            'total_sales'     => Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))
                                      ->where('payment_status', 'paid')->count(),
            'total_revenue'   => $author->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance' => $author->royalties()->where('status', 'pending')->sum('net_amount'),
            'total_views'     => $author->books()->sum('views'),
            'total_downloads' => $author->books()->sum('downloads'),
            'followers_count' => $author->followers()->count(),
            'avg_rating'      => round($author->books()->avg('average_rating') ?? 0, 1),
        ];

        $recentSales = Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))
            ->where('payment_status', 'paid')
            ->with(['book', 'user'])
            ->latest()->limit(8)->get();

        $topBooks = $author->books()
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->orderByDesc('orders_count')->limit(5)->get();

        // MySQL : DATE_FORMAT
        $monthlyRevenue = Royalty::where('author_id', $author->id)
            ->where('status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(net_amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)->get();

        return view('author.dashboard', compact('stats', 'recentSales', 'topBooks', 'monthlyRevenue'));
    }
}
