{{-- resources/views/admin/orders/index.blade.php --}}
@extends('layouts.admin')
@section('title','Commandes – LireX Admin')
@section('page-title','Commandes & Paiements')
@section('page-subtitle','Suivi de toutes les transactions')

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Revenus totaux</p>
    <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_revenue'],0,',',' ') }} XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Revenus aujourd'hui</p>
    <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['today_revenue'],0,',',' ') }} XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Paiements en attente</p>
    <p class="text-2xl font-bold text-amber-600">{{ $summary['pending_count'] }}</p>
  </div>
</div>

<div class="bg-white rounded-2xl p-5 mb-5 shadow-sm border border-slate-100">
  <form method="GET" class="flex gap-4 items-end">
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Tous</option>
        <option value="paid" @selected(request('status')==='paid')>Payé</option>
        <option value="pending" @selected(request('status')==='pending')>En attente</option>
        <option value="failed" @selected(request('status')==='failed')>Échoué</option>
        <option value="refunded" @selected(request('status')==='refunded')>Remboursé</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Méthode</label>
      <select name="method" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Toutes</option>
        <option value="peex">Peex (Mobile Money)</option>
        <option value="mtn_momo">MTN MoMo (historique)</option>
        <option value="airtel_money">Airtel Money (historique)</option>
        <option value="stripe">Stripe</option>
        <option value="free">Gratuit</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        @foreach(['Référence','Livre','Acheteur','Montant','Méthode','Statut','Date'] as $h)
        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $h }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($orders as $order)
      <tr class="border-b border-slate-50 hover:bg-slate-50/50" id="order-row-{{ $order->id }}">
        <td class="px-5 py-3 font-mono text-blue-600 text-xs font-semibold">{{ $order->reference }}</td>
        <td class="px-5 py-3">
          <div class="flex items-center gap-2">
            <img src="{{ $order->book->cover_url }}" class="w-8 h-10 object-cover rounded" alt="">
            <div>
              <p class="font-semibold text-slate-700 max-w-[140px] truncate text-xs">{{ $order->book->title }}</p>
              <p class="text-slate-400 text-xs">{{ $order->book->author->name }}</p>
            </div>
          </div>
        </td>
        <td class="px-5 py-3 text-slate-600">{{ $order->user->name }}</td>
        <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($order->amount,0,',',' ') }} {{ $order->currency }}</td>
        <td class="px-5 py-3">
          @php $m=['peex'=>['Peex','bg-orange-100 text-orange-800'],'mtn_momo'=>['MTN','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel','bg-red-100 text-red-800'],'stripe'=>['Carte','bg-blue-100 text-blue-800'],'free'=>['Gratuit','bg-green-100 text-green-800']]; @endphp
          @if(isset($m[$order->payment_method]))
          <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $m[$order->payment_method][1] }}">{{ $m[$order->payment_method][0] }}</span>
          @endif
        </td>
        <td class="px-5 py-3">
          @php $s=['paid'=>'badge-published','pending'=>'badge-pending','failed'=>'badge-rejected','refunded'=>'badge-draft']; @endphp
          <div class="flex items-center gap-2">
            <span class="status-badge {{ $s[$order->payment_status] ?? 'badge-draft' }}">{{ ucfirst($order->payment_status) }}</span>
            @if($order->payment_method === 'peex' && $order->payment_status === 'pending')
            <button type="button" title="Vérifier le statut auprès de Peex"
              onclick="refreshOrderStatus({{ $order->id }}, this)"
              class="text-slate-400 hover:text-blue-600 transition text-xs">
              <i class="fa-solid fa-rotate"></i>
            </button>
            @endif
          </div>
        </td>
        <td class="px-5 py-3 text-slate-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
      </tr>
      @empty
      <tr><td colspan="7" class="px-5 py-14 text-center text-slate-400">Aucune commande</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-slate-100">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const STATUS_BADGE = { paid: 'badge-published', pending: 'badge-pending', failed: 'badge-rejected', refunded: 'badge-draft' };
const STATUS_LABEL = { paid: 'Paid', pending: 'Pending', failed: 'Failed', refunded: 'Refunded' };

async function refreshOrderStatus(orderId, btn) {
  const icon = btn.querySelector('i');
  btn.disabled = true;
  icon.classList.add('fa-spin');

  try {
    const res = await fetch(`/admin/orders/${orderId}/refresh-status`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const json = await res.json();

    if (!res.ok || !json.success) {
      alert(json.message || "Impossible de vérifier le statut.");
      return;
    }

    if (json.status && json.status !== 'pending') {
      const row = document.getElementById(`order-row-${orderId}`);
      const badge = row?.querySelector('.status-badge');
      if (badge) {
        badge.className = 'status-badge ' + (STATUS_BADGE[json.status] || 'badge-draft');
        badge.textContent = STATUS_LABEL[json.status] || json.status;
      }
      btn.remove(); // plus la peine de réactualiser une commande devenue définitive
    } else {
      icon.classList.remove('fa-spin');
      btn.disabled = false;
    }
  } catch (e) {
    alert("Erreur réseau lors de la vérification du statut.");
    icon.classList.remove('fa-spin');
    btn.disabled = false;
  }
}
</script>
@endpush
