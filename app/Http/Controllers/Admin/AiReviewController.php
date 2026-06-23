<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AiReview, AccountRequest, Book, Order, User, ChatConversation, PhysicalStockMovement};
use App\Services\{AiReviewService, ChatService, PhysicalStockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ADMIN — ANALYSES IA
class AiReviewController extends Controller
{
    // AiReviewService injecté uniquement à la demande pour éviter l'erreur si ANTHROPIC_API_KEY non configuré
    public function index(Request $request)
    {
        $reviews = AiReview::with(['book.author'])
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->recommendation,fn($q) => $q->where('recommendation', $request->recommendation))
            ->latest()->paginate(20);
        return view('admin.ai_reviews.index', compact('reviews'));
    }

    public function show(AiReview $aiReview)
    {
        $aiReview->load(['book.author', 'book.category']);
        return view('admin.ai_reviews.show', compact('aiReview'));
    }

    /** Lance une nouvelle analyse IA pour un livre */
    public function analyze(Book $book)
    {
        dispatch(function () use ($book) {
            app(AiReviewService::class)->analyze($book);
        })->afterResponse();

        return back()->with('success', "Analyse IA lancée pour « {$book->title} ».");
    }

    /** L'admin approuve un livre après lecture du rapport IA */
    public function approveWithAi(Request $request, Book $book)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($book, $request) {
            $book->update(['status' => 'published']);
            if ($book->aiReview) {
                $book->aiReview->update(['admin_decision_note' => $request->note]);
            }
            $book->author->notify(new \App\Notifications\BookApproved($book));
        });

        return back()->with('success', "Livre « {$book->title} » approuvé et publié.");
    }
}
