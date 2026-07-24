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

class PublicationPlanController extends Controller
{
    /** Liste des formules disponibles */
    public function index(): JsonResponse
    {
        $plans = PublicationPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    /** Souscrire à une formule */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id'        => 'required|exists:publication_plans,id',
            'billing'        => 'required|in:monthly,annual',
            'payment_method' => 'required|in:peex,stripe,free',
            'phone'          => 'required_if:payment_method,peex|nullable|string',
        ]);

        $plan = PublicationPlan::findOrFail($data['plan_id']);
        $user = Auth::user();
        $price = $data['billing'] === 'annual' ? $plan->price_annual : $plan->price_monthly;

        // Vérifier si déjà abonné à un plan actif
        $existing = AuthorPlan::where('user_id', $user->id)->where('status', 'active')->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Vous avez déjà un plan actif.'], 400);
        }

        $authorPlan = AuthorPlan::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'billing'        => $data['billing'],
            'status'         => $price == 0 ? 'active' : 'pending_payment',
            'amount_paid'    => $price,
            'currency'       => $plan->currency,
            'payment_method' => $data['payment_method'],
            'starts_at'      => $price == 0 ? now() : null,
            'ends_at'        => $price == 0 ? now()->addMonth() : null,
        ]);

        if ($price == 0) {
            // Plan gratuit → activer le rôle auteur immédiatement
            $user->update(['role' => 'author']);
        }

        return response()->json(['success' => true, 'data' => $authorPlan->load('plan')], 201);
    }

    /** Plan actif de l'utilisateur connecté */
    public function myPlan(): JsonResponse
    {
        $plan = AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('plan')
            ->first();
        return response()->json(['success' => true, 'data' => $plan]);
    }
}
