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

class CitationController extends Controller
{
    public function __construct(private readonly AiReviewService $aiService) {}

    public function generate(Request $request, Book $book): JsonResponse
    {
        $request->validate(['style' => 'required|in:apa,mla,chicago,ieee,harvard']);

        $book->load('author:id,name');
        $text = $this->aiService->generateCitation($book, $request->style);

        // Sauvegarder pour l'historique
        if (Auth::check()) {
            Citation::updateOrCreate(
                ['book_id' => $book->id, 'user_id' => Auth::id(), 'style' => $request->style],
                ['citation_text' => $text]
            );
        }

        return response()->json(['success' => true, 'data' => ['citation' => $text, 'style' => $request->style]]);
    }

    public function myCitations(): JsonResponse
    {
        $citations = Citation::where('user_id', Auth::id())
            ->with('book:id,title,cover_image')
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $citations]);
    }
}
