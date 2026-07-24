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
            'payment_method' => 'required|in:peex,stripe,free',
            'phone'          => 'required_if:payment_method,peex|nullable|string|max:20',
            'country'        => 'nullable|string|size:2',
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
        // Peex sécurise ses callbacks par Basic Auth (voir doc "Notifications").
        if ($method === 'peex') {
            $expectedUser = config('services.peex.callback_username');
            $expectedPass = config('services.peex.callback_password');
            if ($expectedPass && !($request->getUser() === $expectedUser && $request->getPassword() === $expectedPass)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
        }

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

    /**
     * Téléchargement désactivé volontairement : un livre acheté se lit uniquement
     * dans l'espace lecteur (streaming via readLink()/streamBook()), il n'est
     * jamais téléchargeable sous forme de fichier brut.
     */
    public function downloadLink(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) abort(403);

        return response()->json([
            'success' => false,
            'message' => "Le téléchargement n'est pas autorisé. Retrouvez ce livre dans votre bibliothèque pour le lire en ligne.",
        ], 403);
    }
}
