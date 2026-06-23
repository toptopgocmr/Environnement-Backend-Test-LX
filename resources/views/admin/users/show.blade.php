@extends('layouts.admin')
@section('title','Profil utilisateur – LireX Admin')
@section('page-title', $user->name)
@section('page-subtitle', $user->email)

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
  <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
</div>
@endif

<div class="grid grid-cols-3 gap-6">

  {{-- Profil --}}
  <div class="col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="text-center mb-4">
      <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover mx-auto ring-2 ring-blue-600">
      <p class="font-bold text-slate-800 mt-3">{{ $user->name }}</p>
      <p class="text-slate-400 text-sm">{{ $user->email }}</p>
      <span class="badge-draft mt-2 inline-block">{{ ucfirst($user->role) }}</span>
      @if(!$user->is_active)
        <span class="badge-rejected mt-2 inline-block ml-1">Suspendu</span>
      @endif
    </div>

    <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
      <div class="flex justify-between"><span class="text-slate-400">Téléphone</span><span class="font-medium">{{ $user->phone ?? '—' }}</span></div>
      <div class="flex justify-between"><span class="text-slate-400">Ville</span><span class="font-medium">{{ $user->city ?? '—' }}</span></div>
      <div class="flex justify-between"><span class="text-slate-400">Pays</span><span class="font-medium">{{ $user->country ?? '—' }}</span></div>
      @if($user->domain)
      <div class="flex justify-between"><span class="text-slate-400">Domaine</span><span class="font-medium text-right">{{ $user->domain }}</span></div>
      @endif
      <div class="flex justify-between"><span class="text-slate-400">Inscrit le</span><span class="font-medium">{{ $user->created_at->format('d/m/Y') }}</span></div>
    </div>

    <div class="mt-5 space-y-2">
      <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
        @csrf
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium {{ $user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
          {{ $user->is_active ? 'Suspendre le compte' : 'Réactiver le compte' }}
        </button>
      </form>

      @if($user->role !== 'author' || !$user->is_verified_author)
      <form method="POST" action="{{ route('admin.users.verify-author', $user) }}">
        @csrf
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">
          Vérifier comme auteur
        </button>
      </form>
      @endif

      <form method="POST" action="{{ route('admin.users.chat-author', $user) }}">
        @csrf
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium bg-slate-50 text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-comments"></i> Démarrer une conversation
        </button>
      </form>

      <button onclick="document.getElementById('bioModal').classList.remove('hidden')"
              class="w-full py-2 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
        <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier la biographie
      </button>
    </div>
  </div>

  {{-- Stats + activité --}}
  <div class="col-span-2 space-y-6">
    <div class="grid grid-cols-4 gap-4">
      <div class="stat-card text-center"><p class="text-2xl font-bold text-blue-600">{{ $stats['books_count'] }}</p><p class="text-slate-500 text-xs mt-1">Livres</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-green-600">{{ $stats['orders_count'] }}</p><p class="text-slate-500 text-xs mt-1">Achats</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-amber-600">{{ number_format($stats['total_earnings'], 0, ',', ' ') }}</p><p class="text-slate-500 text-xs mt-1">Gagné (XAF)</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-purple-600">{{ number_format($stats['pending_balance'], 0, ',', ' ') }}</p><p class="text-slate-500 text-xs mt-1">Solde (XAF)</p></div>
    </div>

    {{-- Biographie --}}
    @if($user->bio || $user->role === 'author')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="font-semibold text-slate-800">Biographie</p>
        <button onclick="document.getElementById('bioModal').classList.remove('hidden')"
                class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
          <i class="fa-solid fa-pen text-xs"></i> Modifier
        </button>
      </div>
      @if($user->bio)
        <p class="text-slate-500 text-sm leading-relaxed">{{ $user->bio }}</p>
      @else
        <p class="text-slate-400 text-sm italic">Aucune biographie renseignée.</p>
      @endif
    </div>
    @endif

    @if($user->books->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <p class="font-semibold text-slate-800 mb-3">Livres publiés</p>
      <div class="space-y-2">
        @foreach($user->books->take(5) as $book)
          <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50 last:border-0">
            <a href="{{ route('admin.books.show', $book) }}" class="text-slate-700 hover:text-blue-600 hover:underline">{{ $book->title }}</a>
            <span class="badge-{{ $book->status === 'published' ? 'published' : ($book->status === 'pending' ? 'pending' : 'draft') }}">{{ ucfirst($book->status) }}</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($user->orders->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <p class="font-semibold text-slate-800 mb-3">Achats récents</p>
      <div class="space-y-2">
        @foreach($user->orders->take(5) as $order)
          <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50 last:border-0">
            <span class="text-slate-700">{{ $order->book->title ?? '—' }}</span>
            <span class="text-slate-500">{{ number_format($order->amount, 0, ',', ' ') }} {{ $order->currency }}</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>

{{-- Modal Biographie --}}
<div id="bioModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-bold text-slate-800 text-lg">Modifier la biographie — {{ $user->name }}</h3>
      <button onclick="document.getElementById('bioModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark text-xl"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('admin.users.update-bio', $user) }}">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Domaine / Spécialité</label>
        <input type="text" name="domain" value="{{ old('domain', $user->domain) }}"
               placeholder="ex: Roman - Poésie - Essai"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div class="mb-5">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Biographie</label>
        <textarea name="bio" rows="8" placeholder="Biographie de l'auteur…"
                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('bio', $user->bio) }}</textarea>
        <p class="text-xs text-slate-400 mt-1">Maximum 3000 caractères</p>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('bioModal').classList.add('hidden')"
                class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">
          Annuler
        </button>
        <button type="submit"
                class="flex-1 bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700">
          <i class="fa-solid fa-floppy-disk mr-1"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
