@extends('layouts.admin')
@section('title', 'Paiements en attente')
@section('page-title', 'Paiements en attente')

@section('content')

@if(session('success'))
<div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
  <i class="fa-solid fa-check-circle text-green-500"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
  <i class="fa-solid fa-circle-xmark text-red-500"></i> {{ session('error') }}
</div>
@endif

@if($pending->isEmpty())
<div class="flex flex-col items-center justify-center py-24 text-slate-400">
  <i class="fa-solid fa-circle-check text-5xl mb-4 text-green-300"></i>
  <p class="font-semibold text-lg">Aucun paiement en attente</p>
  <p class="text-sm mt-1">Tous les abonnements ont été traités.</p>
</div>
@else
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
    <h2 class="font-bold text-slate-800">
      <i class="fa-solid fa-clock text-amber-500 mr-2"></i>
      {{ $pending->total() }} paiement(s) à valider
    </h2>
  </div>
  <div class="divide-y divide-slate-100">
    @foreach($pending as $ap)
    @php
      $methodIcon  = match($ap->payment_method) { 'peex'=>'📱', 'mtn_momo'=>'🟡', 'airtel_money'=>'🔴', default=>'💳' };
      $methodLabel = match($ap->payment_method) { 'peex'=>'Peex', 'mtn_momo'=>'MTN MoMo', 'airtel_money'=>'Airtel Money', default=>'Carte' };
    @endphp
    <div class="px-6 py-5 flex items-center gap-5">
      {{-- Avatar --}}
      <img src="{{ $ap->user->avatar_url }}" class="w-11 h-11 rounded-xl object-cover flex-shrink-0" alt="">

      {{-- Infos auteur --}}
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-semibold text-slate-800">{{ $ap->user->name }}</span>
          <span class="text-slate-400 text-sm">{{ $ap->user->email }}</span>
        </div>
        <div class="flex items-center gap-3 mt-1 flex-wrap">
          <span class="text-xs bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-full">
            {{ $ap->plan->name }}
          </span>
          <span class="text-xs text-slate-500">
            {{ $ap->billing === 'annual' ? 'Annuel' : 'Mensuel' }}
          </span>
          <span class="text-xs font-bold text-amber-700">
            {{ number_format($ap->amount_paid, 0, ',', ' ') }} {{ $ap->currency }}
          </span>
          <span class="text-xs text-slate-500">
            {{ $methodIcon }} {{ $methodLabel }}
          </span>
        </div>
        @if($ap->transaction_id)
        <div class="mt-1 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-1.5 inline-block">
          <i class="fa-solid fa-receipt text-slate-400 mr-1"></i>
          <strong>Référence :</strong> {{ $ap->transaction_id }}
        </div>
        @else
        <div class="mt-1 text-xs text-slate-400 italic">Aucune référence fournie</div>
        @endif
        <div class="text-xs text-slate-400 mt-1">
          Soumis le {{ $ap->created_at->format('d/m/Y à H:i') }}
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex flex-col gap-2 flex-shrink-0">
        <form method="POST" action="{{ route('admin.payments.approve', $ap) }}">
          @csrf
          <button type="submit"
            onclick="return confirm('Valider le paiement de {{ addslashes($ap->user->name) }} pour le forfait {{ addslashes($ap->plan->name) }} ?')"
            class="w-full px-4 py-2 rounded-xl text-xs font-bold text-white bg-green-500 hover:bg-green-600 transition flex items-center gap-2">
            <i class="fa-solid fa-check"></i> Valider
          </button>
        </form>
        <form method="POST" action="{{ route('admin.payments.reject', $ap) }}">
          @csrf
          <button type="submit"
            onclick="return confirm('Rejeter ce paiement ?')"
            class="w-full px-4 py-2 rounded-xl text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 transition flex items-center gap-2">
            <i class="fa-solid fa-xmark"></i> Rejeter
          </button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
</div>

<div class="mt-5">{{ $pending->links() }}</div>
@endif

@endsection
