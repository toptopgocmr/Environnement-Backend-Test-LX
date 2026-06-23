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

// ACCOUNT REQUEST (demande d'activation)
class AccountRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'                 => 'required|in:author,auditor,institution',
            'motivation'           => 'required|string|min:50|max:1000',
            'document'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'institution_name'    => 'required_if:type,institution|nullable|string',
            'institution_country' => 'required_if:type,institution|nullable|string',
        ]);

        $existing = AccountRequest::where('user_id', Auth::id())
            ->whereIn('status', ['pending'])
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Demande déjà en cours.'], 400);
        }

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('account_documents', 'local');
        }

        $req = AccountRequest::create(array_merge($data, ['user_id' => Auth::id()]));
        return response()->json(['success' => true, 'data' => $req, 'message' => 'Demande soumise. Traitement sous 48h.'], 201);
    }

    public function myRequest(): JsonResponse
    {
        $req = AccountRequest::where('user_id', Auth::id())->latest()->first();
        return response()->json(['success' => true, 'data' => $req]);
    }
}
