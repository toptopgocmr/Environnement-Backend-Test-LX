<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{
    ChatConversation, ChatMessage, PublicationPlan, AuthorPlan,
    AccountRequest, Book, Order, ShippingAddress, ReadingSession, Citation
};
use App\Services\{ChatService, AiReviewService, PhysicalStockService};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Illuminate\Support\Str;

// SESSIONS DE LECTURE (location)
class ReadingSessionController extends Controller
{
    public function rent(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'duration_hours' => 'required|integer|in:6,24,72',
            'payment_method' => 'required|in:peex,stripe',
            'phone'          => 'required_if:payment_method,peex|nullable|string',
        ]);

        if (!$book->allow_rental) {
            return response()->json(['success' => false, 'message' => 'Ce livre ne propose pas la location.'], 400);
        }

        $user  = Auth::user();
        $hours = $request->duration_hours;
        $price = ($book->rental_price_hour ?? 0) * $hours;

        // Vérifier session active existante
        $existing = ReadingSession::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'data' => $existing, 'message' => 'Session déjà active.']);
        }

        $session = ReadingSession::create([
            'user_id'        => $user->id,
            'book_id'        => $book->id,
            'token'          => Str::random(64),
            'status'         => $price == 0 ? 'active' : 'active', // à conditionner au paiement en prod
            'amount_paid'    => $price,
            'currency'       => $book->currency,
            'duration_hours' => $hours,
            'starts_at'      => now(),
            'expires_at'     => now()->addHours($hours),
        ]);

        return response()->json(['success' => true, 'data' => $session], 201);
    }

    public function myActiveSessions(): JsonResponse
    {
        $sessions = ReadingSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('book:id,title,cover_image,author_id')
            ->get();
        return response()->json(['success' => true, 'data' => $sessions]);
    }
}
