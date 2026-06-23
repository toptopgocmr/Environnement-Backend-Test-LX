{{-- resources/views/author/books/index.blade.php --}}
@extends('layouts.author')
@section('title','Mes Livres')
@section('page-title','Mes Livres')
@section('page-subtitle','Gérez votre catalogue')

@section('content')
{{-- Bannière : forfait requis ──────────────────────────────────────────── --}}
@php
  $activePlan = \App\Models\AuthorPlan::where('user_id', Auth::id())
      ->where('status','active')->with('plan')->latest()->first();
  $hasPlan = $activePlan && $activePlan->isActive();
@endphp

@unless($hasPlan)
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;">
  <i class="fa-solid fa-triangle-exclamation" style="color:#e67e00;font-size:22px;flex-shrink:0;"></i>
  <div style="flex:1;">
    <strong style="color:#7a4900;">Aucun forfait actif</strong>
    <p style="color:#7a4900;margin:2px 0 0;font-size:13px;">Vous ne pouvez pas publier de nouveau livre sans forfait. Souscrivez à un forfait pour accéder à la publication.</p>
  </div>
  <a href="{{ route('author.plans.index') }}"
     style="background:#ff9900;color:#fff;border-radius:7px;padding:8px 18px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
    Voir les forfaits
  </a>
</div>
@endunless

@if($books->isEmpty())
<div class="flex flex-col items-center justify-center py-24 text-center">
  <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center mb-5">
    <i class="fa-solid fa-book-open text-blue-300 text-4xl"></i>
  </div>
  <h3 class="text-xl font-bold text-slate-700 mb-2">Aucun livre publié</h3>
  <p class="text-slate-400 mb-6">Commencez dès maintenant — publiez votre premier ouvrage sur LireX.</p>
  @if($hasPlan)
  <a href="{{ route('author.books.create') }}" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-blue-700 transition">
    <i class="fa-solid fa-plus mr-2"></i>Publier un livre
  </a>
  @else
  <a href="{{ route('author.plans.index') }}" class="bg-amber-500 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-amber-600 transition">
    <i class="fa-solid fa-crown mr-2"></i>Choisir un forfait
  </a>
  @endif
</div>
@else
<div class="grid grid-cols-3 gap-5">
  @foreach($books as $book)
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-md transition">
    <div class="relative">
      <img src="{{ $book->cover_url }}" class="w-full h-44 object-cover" alt="">
      <div class="absolute top-3 left-3">
        @php $bc=['published'=>'bg-green-500','pending'=>'bg-amber-500','rejected'=>'bg-red-500','draft'=>'bg-slate-500','suspended'=>'bg-orange-500']; @endphp
        <span class="text-white text-xs font-semibold px-2.5 py-1 rounded-full {{ $bc[$book->status] ?? 'bg-slate-500' }}">{{ ucfirst($book->status) }}</span>
      </div>
      @if($book->is_featured)
      <div class="absolute top-3 right-3 bg-amber-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">★</div>
      @endif
    </div>
    <div class="p-5">
      <h3 class="font-bold text-slate-800 mb-1 line-clamp-1">{{ $book->title }}</h3>
      <p class="text-blue-600 font-bold text-lg">{{ $book->price_formatted }}</p>
      <div class="flex items-center justify-between mt-3 text-xs text-slate-400">
        <span><i class="fa-solid fa-cart-shopping mr-1"></i>{{ $book->orders_count }} ventes</span>
        <span><i class="fa-solid fa-eye mr-1"></i>{{ number_format($book->views) }} vues</span>
        <span><i class="fa-solid fa-star mr-1 text-amber-400"></i>{{ $book->average_rating }}</span>
      </div>
      <div class="flex gap-2 mt-4">
        <a href="{{ route('author.books.stats',$book) }}" class="flex-1 text-center bg-blue-50 text-blue-600 py-2 rounded-xl text-xs font-semibold hover:bg-blue-100 transition">Stats</a>
        @if(in_array($book->status,['draft','rejected']))
        <a href="{{ route('author.books.edit',$book) }}" class="flex-1 text-center bg-slate-50 text-slate-600 py-2 rounded-xl text-xs font-semibold hover:bg-slate-100 transition">Modifier</a>
        @endif
        @if(in_array($book->status,['draft','rejected']))
        <form method="POST" action="{{ route('author.books.destroy',$book) }}" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button class="bg-red-50 text-red-500 py-2 px-3 rounded-xl text-xs hover:bg-red-100 transition"><i class="fa-solid fa-trash"></i></button>
        </form>
        @endif
      </div>
      @if($book->status==='rejected' && $book->rejection_reason)
      <div class="mt-3 bg-red-50 border border-red-100 rounded-xl p-3 text-xs text-red-600">
        <strong>Rejeté :</strong> {{ $book->rejection_reason }}
      </div>
      @endif
    </div>
  </div>
  @endforeach
</div>
<div class="mt-6">{{ $books->links() }}</div>
@endif
@endsection
