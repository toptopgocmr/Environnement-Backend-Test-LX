@extends('layouts.admin')
@section('title','Retraits – LireX Admin')
@section('page-title','Demandes de Retrait')
@section('page-subtitle','Gestion des paiements aux auteurs')

@section('content')
<div class="bg-white rounded-2xl p-5 mb-5 shadow-sm border border-slate-100">
  <form method="GET" class="flex gap-4 items-end">
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Tous</option>
        <option value="pending" @selected(request('status')==='pending')>En attente</option>
        <option value="processing" @selected(request('status')==='processing')>En cours</option>
        <option value="completed" @selected(request('status')==='completed')>Complété</option>
        <option value="rejected" @selected(request('status')==='rejected')>Rejeté</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold">Filtrer</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        @foreach(['Auteur','Montant','Méthode','Compte','Solde avant','Statut','Date','Actions'] as $h)
        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">{{ $h }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($withdrawals as $w)
      <tr class="border-b border-slate-50 hover:bg-slate-50/50">
        <td class="px-5 py-3">
          <div class="flex items-center gap-3">
            <img src="{{ $w->author->avatar_url }}" class="w-8 h-8 rounded-full" alt="">
            <div>
              <p class="font-semibold text-slate-700">{{ $w->author->name }}</p>
              <p class="text-xs text-slate-400">{{ $w->author->email }}</p>
            </div>
          </div>
        </td>
        <td class="px-5 py-3 font-bold text-slate-800">{{ number_format($w->amount,0,',',' ') }} XAF</td>
        <td class="px-5 py-3">
          @php $mc=['mtn_momo'=>['MTN MoMo','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel Money','bg-red-100 text-red-800'],'bank'=>['Virement','bg-blue-100 text-blue-800']]; @endphp
          <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ ($mc[$w->method]??['',''])[1] }}">{{ ($mc[$w->method]??[$w->method,''])[0] }}</span>
        </td>
        <td class="px-5 py-3 text-slate-600 font-mono text-xs">{{ $w->account_number }}</td>
        <td class="px-5 py-3 text-slate-500 text-xs">{{ number_format($w->balance_before,0,',',' ') }} XAF</td>
        <td class="px-5 py-3">
          @php $sc=['pending'=>'badge-pending','processing'=>'bg-blue-100 text-blue-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium','completed'=>'badge-published','rejected'=>'badge-rejected']; @endphp
          <span class="{{ $sc[$w->status] ?? 'badge-draft' }}">{{ ucfirst($w->status) }}</span>
        </td>
        <td class="px-5 py-3 text-slate-400 text-xs">{{ $w->created_at->format('d/m/Y') }}</td>
        <td class="px-5 py-3">
          @if($w->status === 'pending')
          <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.withdrawals.approve',$w) }}">
              @csrf
              <button class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">Traiter</button>
            </form>
            <button onclick="openRejectW({{ $w->id }})" class="px-3 py-1 bg-red-50 text-red-600 border border-red-200 text-xs rounded-lg hover:bg-red-100 transition">Rejeter</button>
          </div>
          @else
          <span class="text-slate-300 text-xs">—</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="8" class="px-5 py-14 text-center text-slate-400">Aucune demande de retrait</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-slate-100">{{ $withdrawals->withQueryString()->links() }}</div>
</div>

<div id="rejectWModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-4">Rejeter la demande</h3>
    <form method="POST" id="rejectWForm">
      @csrf
      <textarea name="reason" rows="3" required placeholder="Raison du rejet…" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('rejectWModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2 text-sm text-slate-600">Annuler</button>
        <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2 text-sm font-semibold">Rejeter</button>
      </div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>function openRejectW(id){ document.getElementById('rejectWForm').action=`/admin/withdrawals/${id}/reject`; document.getElementById('rejectWModal').classList.remove('hidden'); }</script>
@endpush
