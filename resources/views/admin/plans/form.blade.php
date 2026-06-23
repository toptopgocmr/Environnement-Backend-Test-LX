@extends('layouts.admin')
@section('title', ($plan->exists ? 'Modifier' : 'Créer') . ' un forfait – LireX Admin')
@section('page-title', $plan->exists ? 'Modifier le forfait' : 'Nouveau forfait')
@section('page-subtitle', $plan->exists ? '« ' . $plan->name . ' »' : 'Définissez les caractéristiques de l\'offre')

@section('content')
<div class="max-w-3xl">

<form method="POST"
      action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
    @csrf
    @if($plan->exists) @method('PUT') @endif

    {{-- Infos de base --}}
    <div class="stat-card mb-5 space-y-4">
        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide border-b border-slate-100 pb-3">
            <i class="fa-solid fa-tag mr-2 text-[var(--aws-orange)]"></i>Informations générales
        </h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">Nom du forfait *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                    placeholder="ex: Auteur Premium"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">Ordre d'affichage *</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
        </div>

        <div>
            <label class="block text-slate-600 text-xs font-semibold mb-1.5">Description</label>
            <input type="text" name="description" value="{{ old('description', $plan->description) }}"
                placeholder="Courte description affichée sous le nom"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
        </div>

        <div>
            <label class="block text-slate-600 text-xs font-semibold mb-1.5">
                Avantages (une ligne = un avantage)
            </label>
            <textarea name="features_raw" rows="4"
                placeholder="Support prioritaire&#10;Badge auteur vérifié&#10;Analytics avancés"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 font-mono">{{ old('features_raw', $plan->exists ? implode("\n", $plan->features ?? []) : '') }}</textarea>
            <p class="text-slate-400 text-xs mt-1">Chaque ligne devient un item dans la liste des avantages.</p>
        </div>
    </div>

    {{-- Tarification --}}
    <div class="stat-card mb-5 space-y-4">
        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide border-b border-slate-100 pb-3">
            <i class="fa-solid fa-money-bill mr-2 text-[var(--aws-orange)]"></i>Tarification
        </h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">Prix mensuel *</label>
                <div class="relative">
                    <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}"
                        min="0" step="100"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <span class="absolute right-3 top-2.5 text-slate-400 text-xs">XAF</span>
                </div>
                <p class="text-slate-400 text-xs mt-1">Mettre 0 = Gratuit</p>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">Prix annuel *</label>
                <div class="relative">
                    <input type="number" name="price_annual" value="{{ old('price_annual', $plan->price_annual ?? 0) }}"
                        min="0" step="100"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <span class="absolute right-3 top-2.5 text-slate-400 text-xs">XAF</span>
                </div>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">Devise *</label>
                <select name="currency"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <option value="XAF" {{ old('currency', $plan->currency) === 'XAF' ? 'selected' : '' }}>XAF (Franc CFA)</option>
                    <option value="USD" {{ old('currency', $plan->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency', $plan->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-slate-600 text-xs font-semibold mb-1.5">
                Taux de royalties reversé à l'auteur (%) *
            </label>
            <div class="flex items-center gap-3">
                <input type="range" name="royalty_percent" id="royalty-range"
                    min="0" max="100" step="1"
                    value="{{ old('royalty_percent', $plan->royalty_percent ?? 70) }}"
                    oninput="document.getElementById('royalty-display').textContent = this.value + '%'"
                    class="flex-1 accent-orange-500">
                <span id="royalty-display" class="text-lg font-bold text-orange-500 w-14 text-right">
                    {{ old('royalty_percent', $plan->royalty_percent ?? 70) }}%
                </span>
            </div>
            <p class="text-slate-400 text-xs mt-1">La plateforme conserve {{ 100 - ($plan->royalty_percent ?? 70) }}% de commission.</p>
        </div>
    </div>

    {{-- Limites --}}
    <div class="stat-card mb-5 space-y-4">
        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide border-b border-slate-100 pb-3">
            <i class="fa-solid fa-sliders mr-2 text-[var(--aws-orange)]"></i>Limites & permissions
        </h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">
                    Nombre de livres max *
                </label>
                <input type="number" name="max_books" value="{{ old('max_books', $plan->max_books ?? 1) }}"
                    min="-1"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                <p class="text-slate-400 text-xs mt-1">Mettre -1 = illimité</p>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5">
                    Taille max fichier (Mo) *
                </label>
                <input type="number" name="max_file_size_mb" value="{{ old('max_file_size_mb', $plan->max_file_size_mb ?? 500) }}"
                    min="1"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @php
            $toggles = [
                ['name'=>'allow_physical', 'label'=>'Vente physique (impression)', 'icon'=>'fa-truck'],
                ['name'=>'allow_academic', 'label'=>'Documents académiques (thèses, mémoires)', 'icon'=>'fa-graduation-cap'],
                ['name'=>'allow_audio',    'label'=>'Livres audio', 'icon'=>'fa-headphones'],
                ['name'=>'ai_review',      'label'=>'Analyse IA obligatoire', 'icon'=>'fa-robot'],
                ['name'=>'is_active',      'label'=>'Forfait visible & actif', 'icon'=>'fa-eye'],
            ];
            @endphp
            @foreach($toggles as $t)
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                <input type="checkbox" name="{{ $t['name'] }}" value="1"
                    {{ old($t['name'], $plan->{$t['name']} ?? false) ? 'checked' : '' }}
                    class="w-4 h-4 accent-orange-500">
                <i class="fa-solid {{ $t['icon'] }} text-slate-400 w-4"></i>
                <span class="text-sm text-slate-700">{{ $t['label'] }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="btn-aws px-6 py-2.5 text-sm">
            <i class="fa-solid fa-{{ $plan->exists ? 'floppy-disk' : 'plus' }} mr-2"></i>
            {{ $plan->exists ? 'Enregistrer les modifications' : 'Créer le forfait' }}
        </button>
        <a href="{{ route('admin.plans.index') }}"
           class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
            Annuler
        </a>
        @if($plan->exists)
        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
              class="ml-auto"
              onsubmit="return confirm('Supprimer définitivement ce forfait ?')">
            @csrf @method('DELETE')
            <button type="submit"
                class="px-5 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm font-medium hover:bg-red-100 transition">
                <i class="fa-solid fa-trash mr-2"></i>Supprimer
            </button>
        </form>
        @endif
    </div>

    @if($errors->any())
    <div class="mt-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="space-y-1 list-disc list-inside">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif
</form>

</div>
@endsection
