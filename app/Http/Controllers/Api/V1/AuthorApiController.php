<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthorApiController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $author = Auth::user();
        return response()->json(['success' => true, 'data' => [
            'stats' => [
                'books'           => $author->books()->count(),
                'published'       => $author->books()->where('status', 'published')->count(),
                'total_sales'     => Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))->where('payment_status', 'paid')->count(),
                'pending_balance' => $author->pending_balance,
                'total_earned'    => $author->total_earnings,
                'followers'       => $author->followers()->count(),
            ],
        ]]);
    }

    public function myBooks(): JsonResponse
    {
        $books = Auth::user()->books()
            ->with('category')
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function submitBook(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required_unless:is_free,1|numeric|min:0',
            'is_free'     => 'boolean',
            'language'    => 'required|in:fr,en,es,ar,pt,sw',
            'format'      => 'required|in:pdf,epub,both',
            'cover_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'book_file'   => 'required|mimes:pdf,epub|max:51200',
        ]);

        $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        $data['file_path']   = $request->file('book_file')->store('books/' . Auth::id(), 'local');
        $data['author_id']   = Auth::id();
        $data['status']      = 'pending';
        $data['is_free']     = $request->boolean('is_free');

        $book = Book::create($data);
        return response()->json(['success' => true, 'message' => 'Livre soumis pour validation.', 'data' => $book], 201);
    }

    public function earnings(): JsonResponse
    {
        $author    = Auth::user();
        $royalties = \App\Models\Royalty::where('author_id', $author->id)
            ->with('order.book:id,title')
            ->latest()->paginate(20);

        return response()->json(['success' => true, 'data' => [
            'royalties' => $royalties,
            'summary'   => [
                'total_earned'    => $author->total_earnings,
                'pending_balance' => $author->pending_balance,
            ],
        ]]);
    }

    public function withdraw(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $author = Auth::user();
        $data   = $request->validate([
            'amount'  => 'required|numeric|min:1000',
            'method'  => 'required|in:mtn_momo,airtel_money,bank',
            'phone'   => 'nullable|string|max:20',
            'details' => 'nullable|string|max:255',
        ]);

        $pending = $author->royalties()->where('status', 'pending')->sum('net_amount');
        if ($data['amount'] > $pending) {
            return response()->json(['success' => false, 'message' => 'Solde insuffisant.'], 422);
        }

        \App\Models\WithdrawalRequest::create(array_merge($data, [
            'author_id'      => $author->id,
            'status'         => 'pending',
            'balance_before' => $pending,
        ]));

        return response()->json(['success' => true, 'message' => 'Demande de retrait soumise avec succès.']);
    }
}
