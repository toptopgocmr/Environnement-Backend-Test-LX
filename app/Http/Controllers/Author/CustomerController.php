<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Order, Book};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $authorId = Auth::id();

        // IDs des livres de l'auteur
        $bookIds = Book::where('author_id', $authorId)->pluck('id');

        // Commandes payées sur ces livres, groupées par acheteur
        $query = Order::with(['user', 'book'])
            ->whereIn('book_id', $bookIds)
            ->where('payment_status', 'paid')
            ->latest();

        // Filtre par livre
        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filtre par recherche (nom ou email)
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Stats rapides
        $totalCustomers = Order::whereIn('book_id', $bookIds)
            ->where('payment_status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        $totalRevenue = Order::whereIn('book_id', $bookIds)
            ->where('payment_status', 'paid')
            ->sum('amount');

        // Livres pour le filtre
        $books = Book::where('author_id', $authorId)
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('author.customers.index', compact(
            'orders', 'books', 'totalCustomers', 'totalRevenue'
        ));
    }
}
