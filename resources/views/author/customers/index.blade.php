@extends('layouts.author')

@section('title', 'Mes Clients')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

  {{-- En-tête --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Mes Clients</h1>
      <p class="text-slate-500 text-sm mt-1">Lecteurs qui ont acheté vos livres</p>
    </div>
    <div class="flex gap-3">
      <div class="rounded-xl px-4 py-2 text-center" style="background:#fff3cd;border:1.5px solid #f59e0b;">
        <div class="text-xl font-black text-amber-600">{{ number_format($totalCustomers) }}</div>
        <div class="text-xs text-slate-600">Clients uniques</div>
      </div>
      <div class="rounded-xl px-4 py-2 text-center" style="background:#dcfce7;border:1.5px solid #22c55e;">
        <div class="text-xl font-black text-green-600">{{ number_format($totalRevenue, 0, ',', ' ') }} XAF</div>
        <div class="text-xs text-slate-600">Revenus totaux</div>
      </div>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
  @endif

  {{-- Filtres --}}
  <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
      placeholder="Rechercher un client (nom ou email)…"
      class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">

    <select name="book_id" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
      <option value="">Tous les livres</option>
      @foreach($books as $book)
        <option value="{{ $book->id }}" @selected(request('book_id') == $book->id)>{{ Str::limit($book->title, 40) }}</option>
      @endforeach
    </select>

    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#ff9900;">
      <i class="fa-solid fa-magnifying-glass mr-1"></i> Filtrer
    </button>
    @if(request('search') || request('book_id'))
      <a href="{{ route('author.customers.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 text-slate-600 hover:bg-slate-50 flex items-center">
        <i class="fa-solid fa-xmark mr-1"></i> Réinitialiser
      </a>
    @endif
  </form>

  {{-- Tableau --}}
  @if($orders->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-slate-200">
      <div class="text-5xl mb-4">👥</div>
      <h3 class="text-lg font-semibold text-slate-700 mb-2">Aucun client pour l'instant</h3>
      <p class="text-slate-400 text-sm">Vos acheteurs apparaîtront ici dès leur premier achat.</p>
    </div>
  @else
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100" style="background:#f8fafc;">
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Client</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Livre acheté</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Montant</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Méthode</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($orders as $order)
            <tr class="hover:bg-slate-50 transition">
              {{-- Client --}}
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                    style="background: hsl({{ (ord($order->user->name[0] ?? 'A')) * 5 % 360 }}, 60%, 50%);">
                    {{ strtoupper(substr($order->user->name ?? '?', 0, 1)) }}
                  </div>
                  <div>
                    <div class="font-semibold text-slate-800">{{ $order->user->name ?? '—' }}</div>
                    <div class="text-xs text-slate-400">{{ $order->user->email ?? '' }}</div>
                  </div>
                </div>
              </td>
              {{-- Livre --}}
              <td class="px-5 py-3.5">
                <div class="font-medium text-slate-700 max-w-xs truncate">{{ $order->book->title ?? '—' }}</div>
                <div class="text-xs text-slate-400">{{ strtoupper($order->book->document_type ?? '') }}</div>
              </td>
              {{-- Montant --}}
              <td class="px-5 py-3.5">
                <span class="font-bold text-green-600">
                  {{ $order->amount == 0 ? 'Gratuit' : number_format($order->amount, 0, ',', ' ') . ' ' . $order->currency }}
                </span>
              </td>
              {{-- Méthode --}}
              <td class="px-5 py-3.5 text-slate-600">
                @php $m = $order->payment_method ?? '—'; @endphp
                @if($m === 'mtn_momo') 🟡 MTN MoMo
                @elseif($m === 'airtel_money') 🔴 Airtel
                @elseif($m === 'stripe') 💳 Stripe
                @else {{ $m }}
                @endif
              </td>
              {{-- Date --}}
              <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                {{ $order->created_at->format('d/m/Y') }}<br>
                <span class="text-slate-400">{{ $order->created_at->format('H:i') }}</span>
              </td>
              {{-- Statut --}}
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                  @if($order->payment_status === 'paid') bg-green-100 text-green-700
                  @elseif($order->payment_status === 'pending') bg-amber-100 text-amber-700
                  @else bg-red-100 text-red-700
                  @endif">
                  @if($order->payment_status === 'paid') ✓ Payé
                  @elseif($order->payment_status === 'pending') En attente
                  @else Échoué
                  @endif
                </span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
          {{ $orders->links() }}
        </div>
      @endif
    </div>

    <p class="text-xs text-slate-400 mt-3">{{ $orders->total() }} achat(s) au total</p>
  @endif

</div>
@endsection
