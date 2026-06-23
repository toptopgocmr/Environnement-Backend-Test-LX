@extends('layouts.author')
@section('title', 'Statistiques – ' . $book->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start gap-5">
        <a href="{{ route('author.books.index') }}" class="p-2 rounded-xl hover:bg-slate-700/30 text-slate-400 transition mt-1">←</a>
        <img src="{{ $book->cover_url }}" alt="Couverture" class="w-16 h-20 rounded-xl object-cover shadow-lg">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $book->title }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $book->category->name ?? '—' }} · {{ $book->pages ?? '—' }} pages</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-green-900/40 text-green-400">Publié</span>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $kpis = [
                ['label'=>'Ventes totales','value'=> $stats['total_sales'],'icon'=>'🛒','color'=>'blue'],
                ['label'=>'Revenus générés','value'=> number_format($stats['total_revenue'],0,',',' ').' FCFA','icon'=>'💰','color'=>'green'],
                ['label'=>'Royalties perçues','value'=> number_format($stats['royalties'],0,',',' ').' FCFA','icon'=>'🏆','color'=>'yellow'],
                ['label'=>'Vues / Préviews','value'=> $stats['views'] ?? 0,'icon'=>'👁️','color'=>'purple'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            <div class="rounded-2xl p-5" style="background:#162035;border:1px solid #1E3A6A">
                <div class="text-2xl mb-2">{{ $kpi['icon'] }}</div>
                <div class="text-2xl font-bold text-white">{{ $kpi['value'] }}</div>
                <div class="text-slate-400 text-xs mt-1">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="rounded-2xl p-6" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold mb-4">Ventes par mois</h2>
            <canvas id="salesChart" height="220"></canvas>
        </div>
        <div class="rounded-2xl p-6" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold mb-4">Répartition par méthode de paiement</h2>
            <canvas id="paymentChart" height="220"></canvas>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="rounded-2xl p-6" style="background:#162035;border:1px solid #1E3A6A">
        <h2 class="text-white font-semibold mb-4">Dernières ventes</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-400 border-b" style="border-color:#1E3A6A">
                        <th class="pb-3 text-left font-medium">Acheteur</th>
                        <th class="pb-3 text-left font-medium">Méthode</th>
                        <th class="pb-3 text-right font-medium">Montant</th>
                        <th class="pb-3 text-right font-medium">Royaltie</th>
                        <th class="pb-3 text-right font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:#1E3A6A">
                    @forelse($recentOrders as $order)
                        <tr class="text-slate-300">
                            <td class="py-3">{{ $order->user->name ?? 'Anonyme' }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $order->payment_method === 'mtn_momo' ? 'bg-yellow-900/40 text-yellow-400' : ($order->payment_method === 'airtel_money' ? 'bg-red-900/40 text-red-400' : 'bg-blue-900/40 text-blue-400') }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>
                            <td class="py-3 text-right text-white font-medium">{{ number_format($order->amount,0,',',' ') }} FCFA</td>
                            <td class="py-3 text-right text-green-400">{{ number_format($order->royalty_amount ?? $order->amount*0.8,0,',',' ') }} FCFA</td>
                            <td class="py-3 text-right text-slate-400">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Aucune vente enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartDefaults = { color: '#94A3B8', font: { family: 'Inter' } };
Chart.defaults.color = chartDefaults.color;

// Ventes par mois
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: @json($stats['sales_by_month']['labels'] ?? []),
        datasets: [{
            label: 'Ventes',
            data: @json($stats['sales_by_month']['data'] ?? []),
            backgroundColor: 'rgba(37,99,235,0.7)',
            borderColor: '#2563EB',
            borderRadius: 6,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: '#1E3A6A' } }, y: { grid: { color: '#1E3A6A' }, ticks: { precision: 0 } } } }
});

// Paiements
const payData = @json($stats['payment_methods'] ?? ['MTN'=>0,'Airtel'=>0,'Stripe'=>0]);
new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(payData),
        datasets: [{ data: Object.values(payData), backgroundColor: ['#F59E0B','#EF4444','#3B82F6'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endsection
