@extends('layouts.admin')
@section('title', 'Forfaits – LireX Admin')
@section('page-title', 'Forfaits de publication')
@section('page-subtitle', 'Gérez les offres proposées aux auteurs')

@section('page-actions')
<a href="{{ route('admin.plans.create') }}"
   class="btn-aws flex items-center gap-2">
    <i class="fa-solid fa-plus text-xs"></i> Nouveau forfait
</a>
@endsection

@section('content')

{{-- KPI rapides --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @php
        $total   = $plans->count();
        $active  = $plans->where('is_active', true)->count();
        $subs    = $plans->sum('author_plans_count');
    @endphp
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $total }}</p>
        <p class="text-sm text-slate-500 mt-1">Forfaits total</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-green-600">{{ $active }}</p>
        <p class="text-sm text-slate-500 mt-1">Actifs</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $total - $active }}</p>
        <p class="text-sm text-slate-500 mt-1">Désactivés</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $subs }}</p>
        <p class="text-sm text-slate-500 mt-1">Souscriptions</p>
    </div>
</div>

{{-- Table forfaits --}}
<div class="stat-card p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs text-slate-500 uppercase tracking-wide">
                <th class="px-5 py-3 font-semibold">#</th>
                <th class="px-5 py-3 font-semibold">Forfait</th>
                <th class="px-5 py-3 font-semibold">Prix mensuel</th>
                <th class="px-5 py-3 font-semibold">Prix annuel</th>
                <th class="px-5 py-3 font-semibold">Royalties</th>
                <th class="px-5 py-3 font-semibold">Livres max</th>
                <th class="px-5 py-3 font-semibold">Options</th>
                <th class="px-5 py-3 font-semibold">Souscripteurs</th>
                <th class="px-5 py-3 font-semibold">Statut</th>
                <th class="px-5 py-3 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="px-5 py-4 text-slate-400 font-mono text-xs">{{ $plan->sort_order }}</td>
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-800">{{ $plan->name }}</p>
                    <p class="text-slate-400 text-xs mt-0.5 max-w-xs truncate">{{ $plan->description }}</p>
                </td>
                <td class="px-5 py-4 font-semibold text-slate-700">
                    {{ $plan->price_monthly == 0 ? 'Gratuit' : number_format($plan->price_monthly, 0, ',', ' ') . ' ' . $plan->currency }}
                </td>
                <td class="px-5 py-4 text-slate-600">
                    {{ $plan->price_annual == 0 ? '–' : number_format($plan->price_annual, 0, ',', ' ') . ' ' . $plan->currency }}
                </td>
                <td class="px-5 py-4">
                    <span class="font-bold text-green-600">{{ number_format($plan->royalty_percent, 0) }}%</span>
                </td>
                <td class="px-5 py-4 text-slate-600">
                    {{ $plan->max_books === -1 ? '∞' : $plan->max_books }}
                </td>
                <td class="px-5 py-4">
                    <div class="flex gap-1.5 flex-wrap">
                        @if($plan->allow_physical) <span class="px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700">Physique</span> @endif
                        @if($plan->allow_academic) <span class="px-2 py-0.5 text-xs rounded-full bg-purple-50 text-purple-700">Académique</span> @endif
                        @if($plan->allow_audio)    <span class="px-2 py-0.5 text-xs rounded-full bg-orange-50 text-orange-700">Audio</span> @endif
                        @if($plan->ai_review)      <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600">IA</span> @endif
                    </div>
                </td>
                <td class="px-5 py-4 text-center">
                    <span class="font-bold text-slate-800">{{ $plan->author_plans_count }}</span>
                </td>
                <td class="px-5 py-4">
                    @if($plan->is_active)
                        <span class="badge-published">Actif</span>
                    @else
                        <span class="badge-draft">Inactif</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.plans.edit', $plan) }}"
                           class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">
                           <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.plans.toggle', $plan) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                                {{ $plan->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                {{ $plan->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                              onsubmit="return confirm('Supprimer le forfait « {{ $plan->name }} » ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-5 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl mb-3 block"></i>
                    Aucun forfait créé.
                    <a href="{{ route('admin.plans.create') }}" class="text-blue-600 underline ml-1">Créer le premier</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
