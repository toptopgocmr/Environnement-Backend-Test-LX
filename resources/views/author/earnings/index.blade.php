@extends('layouts.author')
@section('title','Revenus & Retraits')
@section('page-title','Revenus & Retraits')
@section('page-subtitle','Suivez vos gains et gérez vos retraits')

@section('content')
<div class="grid grid-cols-3 gap-5 mb-8">
  <div class="bg-gradient-to-br from-[#0A1628] to-[#1D4ED8] rounded-2xl p-6 text-white shadow-xl col-span-1">
    <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Solde disponible</p>
    <p class="text-3xl font-bold mb-5">{{ number_format($summary['pending_balance'],0,',',' ') }} <span class="text-lg">XAF</span></p>
    <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="w-full bg-white text-blue-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-blue-50 transition">
      <i class="fa-solid fa-money-bill-transfer mr-1"></i> Retirer mes gains
    </button>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-2">Total gagné (payé)</p>
    <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_earned'],0,',',' ') }} XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-2">Total retiré</p>
    <p class="text-2xl font-bold text-slate-700">{{ number_format($summary['total_withdrawn'],0,',',' ') }} XAF</p>
  </div>
</div>

<div class="grid grid-cols-2 gap-6">
  {{-- Royalties --}}
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Historique des royalties</h3>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Livre</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Montant brut</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Ma part</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Statut</th>
        </tr>
      </thead>
      <tbody>
        @forelse($royalties as $r)
        <tr class="border-t border-slate-50 hover:bg-slate-50/50">
          <td class="px-5 py-3 text-slate-700 text-xs max-w-[120px] truncate">{{ $r->order->book->title ?? '—' }}</td>
          <td class="px-5 py-3 text-slate-600 text-xs">{{ number_format($r->gross_amount,0,',',' ') }} XAF</td>
          <td class="px-5 py-3 font-bold text-green-600 text-xs">{{ number_format($r->net_amount,0,',',' ') }} XAF</td>
          <td class="px-5 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $r->status==='paid'?'bg-green-100 text-green-700':'bg-amber-100 text-amber-700' }}">{{ ucfirst($r->status) }}</span>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Aucune royalty pour l'instant</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-slate-100">{{ $royalties->links() }}</div>
  </div>

  {{-- Withdrawal history --}}
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Demandes de retrait</h3>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Montant</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Méthode</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Statut</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
        </tr>
      </thead>
      <tbody>
        @forelse($withdrawals as $w)
        <tr class="border-t border-slate-50 hover:bg-slate-50/50">
          <td class="px-5 py-3 font-bold text-slate-800 text-xs">{{ number_format($w->amount,0,',',' ') }} XAF</td>
          <td class="px-5 py-3 text-slate-500 text-xs">{{ strtoupper(str_replace('_',' ',$w->method)) }}</td>
          <td class="px-5 py-3">
            @php $sc=['pending'=>'bg-amber-100 text-amber-700','completed'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','processing'=>'bg-blue-100 text-blue-700']; @endphp
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$w->status]??'bg-slate-100 text-slate-600' }}">{{ ucfirst($w->status) }}</span>
          </td>
          <td class="px-5 py-3 text-slate-400 text-xs">{{ $w->created_at->format('d/m/Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Aucune demande</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-slate-100">{{ $withdrawals->links() }}</div>
  </div>
</div>

{{-- Withdrawal Modal --}}
<div id="withdrawModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-1">Demander un retrait</h3>
    <p class="text-slate-400 text-sm mb-5">Solde disponible : <strong class="text-green-600">{{ number_format($summary['pending_balance'],0,',',' ') }} XAF</strong></p>
    <form method="POST" action="{{ route('author.earnings.withdraw') }}">
      @csrf
      <div class="space-y-4">
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Montant (min. 5 000 XAF)</label>
          <input type="number" name="amount" min="5000" max="{{ $summary['pending_balance'] }}" required
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex : 25 000"/>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Méthode</label>
          <select name="method" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            <option value="mtn_momo">📱 MTN Mobile Money</option>
            <option value="airtel_money">📲 Airtel Money</option>
            <option value="bank">🏦 Virement bancaire</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Numéro / Compte</label>
          <input type="text" name="account_number" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none" placeholder="+242 06 XXX XX XX"/>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Nom du bénéficiaire</label>
          <input type="text" name="account_name" required value="{{ Auth::user()->name }}" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none"/>
        </div>
      </div>
      <div class="flex gap-3 mt-6">
        <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700">Demander le retrait</button>
      </div>
    </form>
  </div>
</div>
@endsection
