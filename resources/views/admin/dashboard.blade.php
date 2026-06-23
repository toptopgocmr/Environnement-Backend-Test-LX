@extends('layouts.admin')
@section('title', 'Tableau de bord – LireX Admin')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble de la plateforme')

@section('content')
{{-- KPI Row --}}
<div class="grid grid-cols-4 gap-5 mb-8">
  @php
  $kpis = [
    ['label'=>'Utilisateurs',  'value'=> number_format($stats['total_users']),    'icon'=>'fa-users',        'color'=>'blue',  'sub'=>$stats['new_users_today'].' aujourd\'hui'],
    ['label'=>'Auteurs',       'value'=> number_format($stats['total_authors']),  'icon'=>'fa-pen-nib',      'color'=>'purple','sub'=>'dont '.App\Models\User::where('role','author')->where('is_verified_author',true)->count().' vérifiés'],
    ['label'=>'Livres publiés','value'=> number_format($stats['total_books']),    'icon'=>'fa-book-open',    'color'=>'green', 'sub'=>$stats['pending_books'].' en attente'],
    ['label'=>'Revenus totaux','value'=> number_format($stats['total_revenue'],0,',',' ').' XAF','icon'=>'fa-coins','color'=>'amber','sub'=>number_format($stats['total_orders']).' ventes'],
  ];
  @endphp
  @foreach($kpis as $kpi)
  <div class="stat-card">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 bg-{{ $kpi['color'] }}-100 rounded-xl flex items-center justify-center">
        <i class="fa-solid {{ $kpi['icon'] }} text-{{ $kpi['color'] }}-600"></i>
      </div>
      <span class="text-xs text-green-600 bg-green-50 rounded-full px-2 py-0.5 font-medium">↑ actif</span>
    </div>
    <p class="text-2xl font-bold text-slate-800 mb-1">{{ $kpi['value'] }}</p>
    <p class="text-sm font-semibold text-slate-600">{{ $kpi['label'] }}</p>
    <p class="text-xs text-slate-400 mt-1">{{ $kpi['sub'] }}</p>
  </div>
  @endforeach
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-3 gap-5 mb-8">
  {{-- Revenue Chart --}}
  <div class="col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Revenus mensuels (XAF)</h3>
      <span class="text-xs text-slate-400">12 derniers mois</span>
    </div>
    <canvas id="revenueChart" height="80"></canvas>
  </div>
  {{-- Top Books --}}
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">Top Livres</h3>
    <div class="space-y-3">
      @foreach($topBooks as $i => $book)
      <div class="flex items-center gap-3">
        <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">{{ $i+1 }}</span>
        <img src="{{ $book->cover_url }}" class="w-9 h-12 object-cover rounded" alt="">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-slate-700 truncate">{{ $book->title }}</p>
          <p class="text-xs text-slate-400">{{ $book->orders_count }} ventes</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Pending items --}}
<div class="grid grid-cols-2 gap-5 mb-8">
  {{-- Pending Books --}}
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Livres en attente de validation</h3>
      <a href="{{ route('admin.books.index') }}?status=pending" class="text-blue-600 text-xs hover:underline">Voir tout</a>
    </div>
    @forelse($pendingBooks as $book)
    <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
      <img src="{{ $book->cover_url }}" class="w-10 h-13 object-cover rounded" alt="">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-700 truncate">{{ $book->title }}</p>
        <p class="text-xs text-slate-400">par {{ $book->author->name }}</p>
        <p class="text-xs text-slate-400">{{ $book->created_at->diffForHumans() }}</p>
      </div>
      <div class="flex gap-2">
        <form method="POST" action="{{ route('admin.books.approve', $book) }}">
          @csrf
          <button class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">✓</button>
        </form>
        <a href="{{ route('admin.books.show', $book) }}" class="px-3 py-1 bg-slate-100 text-slate-600 text-xs rounded-lg hover:bg-slate-200 transition">Voir</a>
      </div>
    </div>
    @empty
    <p class="text-slate-400 text-sm text-center py-4">Aucun livre en attente 🎉</p>
    @endforelse
  </div>

  {{-- Pending Withdrawals --}}
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Demandes de retrait</h3>
      <a href="{{ route('admin.withdrawals.index') }}" class="text-blue-600 text-xs hover:underline">Voir tout</a>
    </div>
    @forelse($pendingWithdrawals as $w)
    <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
      <img src="{{ $w->author->avatar_url }}" class="w-9 h-9 rounded-full object-cover" alt="">
      <div class="flex-1">
        <p class="text-sm font-semibold text-slate-700">{{ $w->author->name }}</p>
        <p class="text-xs text-slate-400">{{ number_format($w->amount,0,',',' ') }} XAF via {{ $w->method }}</p>
      </div>
      <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}">
        @csrf
        <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 transition">Traiter</button>
      </form>
    </div>
    @empty
    <p class="text-slate-400 text-sm text-center py-4">Aucune demande en attente</p>
    @endforelse
  </div>
</div>

{{-- Recent Orders --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800">Dernières commandes</h3>
    <a href="{{ route('admin.orders.index') }}" class="text-blue-600 text-xs hover:underline">Voir tout</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
          <th class="pb-3 font-semibold">Référence</th>
          <th class="pb-3 font-semibold">Livre</th>
          <th class="pb-3 font-semibold">Acheteur</th>
          <th class="pb-3 font-semibold">Montant</th>
          <th class="pb-3 font-semibold">Méthode</th>
          <th class="pb-3 font-semibold">Date</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentOrders as $order)
        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
          <td class="py-3 font-mono text-blue-600 text-xs font-semibold">{{ $order->reference }}</td>
          <td class="py-3">
            <div class="flex items-center gap-2">
              <img src="{{ $order->book->cover_url }}" class="w-8 h-10 object-cover rounded" alt="">
              <span class="truncate max-w-[150px] text-slate-700">{{ $order->book->title }}</span>
            </div>
          </td>
          <td class="py-3 text-slate-600">{{ $order->user->name }}</td>
          <td class="py-3 font-semibold text-slate-800">{{ number_format($order->amount,0,',',' ') }} {{ $order->currency }}</td>
          <td class="py-3">
            @php $methods=['mtn_momo'=>['MTN MoMo','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel Money','bg-red-100 text-red-800'],'stripe'=>['Carte','bg-blue-100 text-blue-800'],'free'=>['Gratuit','bg-green-100 text-green-800']]; @endphp
            @if(isset($methods[$order->payment_method]))
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $methods[$order->payment_method][1] }}">{{ $methods[$order->payment_method][0] }}</span>
            @endif
          </td>
          <td class="py-3 text-slate-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: @json($revenueChart->pluck('month')),
    datasets: [{
      label: 'Revenus (XAF)',
      data: @json($revenueChart->pluck('total')),
      borderColor: '#2563EB',
      backgroundColor: 'rgba(37,99,235,0.1)',
      borderWidth: 2.5,
      pointRadius: 4,
      pointBackgroundColor: '#2563EB',
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94A3B8' } },
      y: { grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8' } }
    }
  }
});
</script>
@endpush
