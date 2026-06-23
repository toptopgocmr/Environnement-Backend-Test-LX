<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

// ORDERS / PAYMENT API
class OrderController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function initiate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id'        => 'required|exists:books,id',
            'payment_method' => 'required|in:mtn_momo,airtel_money,stripe,free',
            'phone'          => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string|max:20',
            'type'           => 'in:digital,print',
        ]);

        $book = Book::findOrFail($data['book_id']);
        $user = Auth::user();

        if ($user->hasPurchased($book->id)) {
            return response()->json(['success' => false, 'message' => 'Vous possédez déjà ce livre.'], 400);
        }

        if ($book->is_free || $data['payment_method'] === 'free') {
            $order = Order::create([
                'reference'      => 'LRX' . strtoupper(\Str::random(7)),
                'user_id'        => $user->id,
                'book_id'        => $book->id,
                'amount'         => 0,
                'currency'       => $book->currency,
                'type'           => 'digital',
                'payment_method' => 'free',
                'payment_status' => 'paid',
                'download_token' => \Str::random(64),
                'max_downloads'  => 3,
                'expires_at'     => null, // livre gratuit = pas d'expiration
            ]);
            return response()->json(['success' => true, 'data' => ['order' => $order]]);
        }

        $result = $this->paymentService->initiate($data, $book, $user);
        return response()->json(['success' => $result['success'], 'data' => $result]);
    }

    public function callback(Request $request, string $method): JsonResponse
    {
        $result = $this->paymentService->handleCallback($method, $request->all());
        return response()->json($result);
    }

    public function myOrders(): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->with(['book:id,title,cover_image,author_id', 'book.author:id,name'])
            ->where('payment_status', 'paid')
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function downloadLink(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) abort(403);
        if ($order->download_count >= $order->max_downloads) {
            return response()->json(['success' => false, 'message' => 'Limite de téléchargements atteinte.'], 403);
        }
        $order->increment('download_count');
        $url = route('api.books.download', $order->book_id) . '?token=' . $order->download_token;
        return response()->json(['success' => true, 'data' => ['download_url' => $url]]);
    }
}
