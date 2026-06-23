<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{PublicationPlan, AuthorPlan};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = PublicationPlan::withCount('authorPlans')->orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => new PublicationPlan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlanData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $data['features'] = $this->parseFeatures($request->features_raw);
        PublicationPlan::create($data);
        return redirect()->route('admin.plans.index')->with('success', 'Forfait créé.');
    }

    public function edit(PublicationPlan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, PublicationPlan $plan)
    {
        $data = $this->validatePlanData($request);
        $data['features'] = $this->parseFeatures($request->features_raw);
        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success', 'Forfait mis à jour.');
    }

    public function destroy(PublicationPlan $plan)
    {
        // Vérifier qu'il n'y a pas d'abonnés actifs
        $activeCount = AuthorPlan::where('plan_id', $plan->id)->where('status', 'active')->count();
        if ($activeCount > 0) {
            return back()->with('error', "Impossible de supprimer : {$activeCount} auteur(s) actif(s) sur ce forfait.");
        }
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Forfait supprimé.');
    }

    public function toggleActive(PublicationPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Forfait « {$plan->name} » {$status}.");
    }

    // ── Paiements en attente ─────────────────────────────────────────────────

    public function payments()
    {
        $pending = AuthorPlan::where('status', 'pending_payment')
            ->with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.index', compact('pending'));
    }

    public function approvePayment(AuthorPlan $authorPlan)
    {
        if ($authorPlan->status !== 'pending_payment') {
            return back()->with('error', 'Ce paiement n\'est pas en attente.');
        }

        // Désactiver l'abonnement actif précédent de cet auteur
        AuthorPlan::where('user_id', $authorPlan->user_id)
            ->where('status', 'active')
            ->update(['status' => 'superseded']);

        $authorPlan->update(['status' => 'active']);

        return back()->with('success', "Paiement validé — forfait « {$authorPlan->plan->name} » activé pour {$authorPlan->user->name}.");
    }

    public function rejectPayment(AuthorPlan $authorPlan)
    {
        if ($authorPlan->status !== 'pending_payment') {
            return back()->with('error', 'Ce paiement n\'est pas en attente.');
        }

        $authorPlan->update(['status' => 'rejected']);

        return back()->with('success', "Paiement rejeté pour {$authorPlan->user->name}.");
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validatePlanData(Request $request): array
    {
        return $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:500',
            'price_monthly'    => 'required|numeric|min:0',
            'price_annual'     => 'required|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'max_books'        => 'required|integer|min:-1',
            'max_file_size_mb' => 'required|integer|min:1',
            'royalty_percent'  => 'required|numeric|min:0|max:100',
            'allow_physical'   => 'boolean',
            'allow_audio'      => 'boolean',
            'allow_academic'   => 'boolean',
            'ai_review'        => 'boolean',
            'is_active'        => 'boolean',
            'sort_order'       => 'required|integer|min:0',
        ]);
    }

    private function parseFeatures(?string $raw): array
    {
        if (!$raw) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }
}
