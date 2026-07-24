<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{PublicationPlan, AuthorPlan};
use App\Services\PeexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        $plans      = PublicationPlan::where('is_active', true)->orderBy('sort_order')->get();
        $activePlan = AuthorPlan::where('user_id', Auth::id())
                        ->where('status', 'active')
                        ->with('plan')
                        ->latest()
                        ->first();

        return view('author.plans.index', compact('plans', 'activePlan'));
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'plan_id'         => 'required|exists:publication_plans,id',
            'billing'         => 'required|in:monthly,annual',
            'payment_method'  => 'required|in:peex,stripe',
            'transaction_ref' => 'nullable|string|max:100',
            'phone_used'      => 'nullable|string|max:30',
        ]);

        $plan   = PublicationPlan::findOrFail($data['plan_id']);
        $amount = $data['billing'] === 'annual' ? $plan->price_annual : $plan->price_monthly;

        // Forfait gratuit → activer immédiatement sans paiement
        if ($amount == 0) {
            AuthorPlan::where('user_id', Auth::id())
                ->whereIn('status', ['active','pending_payment'])
                ->update(['status' => 'cancelled']);

            AuthorPlan::create([
                'user_id'        => Auth::id(),
                'plan_id'        => $plan->id,
                'billing'        => $data['billing'],
                'status'         => 'active',
                'amount_paid'    => 0,
                'currency'       => $plan->currency,
                'payment_method' => $data['payment_method'],
                'transaction_id' => 'FREE-' . now()->format('YmdHis'),
                'starts_at'      => now(),
                'ends_at'        => $data['billing'] === 'annual' ? now()->addYear() : now()->addMonth(),
            ]);

            return redirect()->route('author.plans.index')
                ->with('success', "Forfait « {$plan->name} » activé avec succès. Vous pouvez désormais publier vos livres !");
        }

        // Forfait payant → mettre en attente de validation admin
        // Mettre en pause les abonnements précédents en attente (pas les actifs)
        AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'pending_payment')
            ->update(['status' => 'cancelled']);

        $txRef = $data['transaction_ref'] ?? null;
        // Enrichir la référence avec le numéro de téléphone si fourni
        if ($data['phone_used'] ?? null) {
            $txRef = ($txRef ? $txRef . ' | ' : '') . 'Tél: ' . $data['phone_used'];
        }

        AuthorPlan::create([
            'user_id'        => Auth::id(),
            'plan_id'        => $plan->id,
            'billing'        => $data['billing'],
            'status'         => 'pending_payment',
            'amount_paid'    => $amount,
            'currency'       => $plan->currency,
            'payment_method' => $data['payment_method'],
            'transaction_id' => $txRef,
            'starts_at'      => now(),
            'ends_at'        => $data['billing'] === 'annual' ? now()->addYear() : now()->addMonth(),
        ]);

        $methodLabel = match($data['payment_method']) {
            'peex'  => 'Mobile Money (Peex)',
            default => 'Carte bancaire',
        };

        return redirect()->route('author.plans.index')
            ->with('success', "Votre demande d'abonnement au forfait « {$plan->name} » ({$methodLabel}) a été enregistrée. Votre forfait sera activé sous 24 heures ouvrées après vérification de votre paiement.");
    }

    /**
     * AJAX — Déclenche un push payment Mobile Money pour un forfait.
     * Crée l'AuthorPlan en pending_payment et appelle l'API MTN/Airtel.
     * Retourne { ok, authorplan_id, message } en JSON.
     */
    public function initiatePayment(Request $request)
    {
        $data = $request->validate([
            'plan_id'        => 'required|exists:publication_plans,id',
            'billing'        => 'required|in:monthly,annual',
            'payment_method' => 'required|in:peex',
            'phone'          => ['required', 'string', 'regex:/^(\+?242|0)?[0-9]{8,9}$/'],
        ]);

        $plan   = PublicationPlan::findOrFail($data['plan_id']);
        $amount = $data['billing'] === 'annual' ? $plan->price_annual : $plan->price_monthly;

        if ($amount <= 0) {
            return response()->json(['ok' => false, 'message' => 'Utilisez le formulaire classique pour les forfaits gratuits.'], 422);
        }

        // Annuler les paiements en attente existants
        AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'pending_payment')
            ->update(['status' => 'cancelled']);

        // Créer l'enregistrement en attente
        $authorPlan = AuthorPlan::create([
            'user_id'        => Auth::id(),
            'plan_id'        => $plan->id,
            'billing'        => $data['billing'],
            'status'         => 'pending_payment',
            'amount_paid'    => $amount,
            'currency'       => $plan->currency,
            'payment_method' => $data['payment_method'],
            'starts_at'      => now(),
            'ends_at'        => $data['billing'] === 'annual' ? now()->addYear() : now()->addMonth(),
        ]);
        $authorPlan->load('plan');

        // Appeler l'API de paiement push (Peex)
        $result = app(PeexService::class)->initiatePlan($authorPlan, $data['phone'], Auth::user()->name);

        if (!$result['success']) {
            $authorPlan->update(['status' => 'cancelled']);
            return response()->json(['ok' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'ok'            => true,
            'authorplan_id' => $authorPlan->id,
            'message'       => $result['message'],
        ]);
    }

    /**
     * AJAX polling — Vérifie le statut d'un paiement Mobile Money en cours.
     * Retourne { status: 'PENDING'|'SUCCESSFUL'|'FAILED', message }
     */
    public function checkStatus(AuthorPlan $authorPlan)
    {
        // Sécurité : l'auteur ne peut vérifier que ses propres paiements
        if ($authorPlan->user_id !== Auth::id()) {
            return response()->json(['status' => 'ERROR', 'message' => 'Accès refusé.'], 403);
        }

        // Si déjà activé (ex: webhook reçu entre temps)
        if ($authorPlan->status === 'active') {
            return response()->json(['status' => 'SUCCESSFUL', 'message' => 'Forfait activé !']);
        }

        if ($authorPlan->status === 'payment_failed') {
            return response()->json(['status' => 'FAILED', 'message' => 'Paiement refusé ou expiré.']);
        }

        $result = match($authorPlan->payment_method) {
            'peex'  => app(PeexService::class)->checkPlanStatus($authorPlan),
            default => ['status' => 'PENDING'],
        };

        $message = match($result['status']) {
            'SUCCESSFUL' => 'Paiement confirmé ! Votre forfait est actif.',
            'FAILED'     => 'Paiement refusé ou annulé. Veuillez réessayer.',
            default      => 'En attente de confirmation sur votre téléphone…',
        };

        return response()->json(['status' => $result['status'], 'message' => $message]);
    }
}
