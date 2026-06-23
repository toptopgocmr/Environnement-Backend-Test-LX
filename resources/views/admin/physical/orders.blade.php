@extends('layouts.admin')
@section('title', 'Commandes physiques – LireX Admin')

@section('content')
<div class="p-6 space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">📦 Commandes physiques</h1>
      <p class="text-gray-500 text-sm mt-1">Suivi des livraisons de livres papier</p>
    </div>
    {{-- Filtre statut --}}
    <div class="flex gap-2">
      @foreach([''=>'Toutes','processing'=>'Préparation','shipped'=>'Expédiées','out_for_delivery'=>'En livraison','delivered'=>'Livrées','failed'=>'Échec'] as $val => $label)
        <a href="{{ request()->fullUrlWithQuery(['status'=>$val]) }}"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition
          {{ request('status')===$val ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
      <p class="text-gray-500 text-sm mt-1">À préparer</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-blue-600">{{ $summary['processing'] }}</p>
      <p class="text-gray-500 text-sm mt-1">En préparation</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-purple-600">{{ $summary['shipped'] }}</p>
      <p class="text-gray-500 text-sm mt-1">En transit</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-green-600">{{ $summary['delivered'] }}</p>
      <p class="text-gray-500 text-sm mt-1">Livrées</p>
    </div>
  </div>

  @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
  @endif

  {{-- Table --}}
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b border-gray-100">
        <tr>
          <th class="px-5 py-3 text-left">Référence</th>
          <th class="px-5 py-3 text-left">Livre</th>
          <th class="px-5 py-3 text-left">Client</th>
          <th class="px-5 py-3 text-left">Destination</th>
          <th class="px-5 py-3 text-left">Suivi</th>
          <th class="px-5 py-3 text-left">Statut</th>
          <th class="px-5 py-3 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @forelse($orders as $order)
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-4 font-mono text-gray-600 text-xs">{{ $order->reference }}</td>
          <td class="px-5 py-4 font-medium text-gray-800 max-w-[150px] truncate">{{ $order->book->title ?? '—' }}</td>
          <td class="px-5 py-4 text-gray-600">
            <p>{{ $order->full_name ?: $order->user->name }}</p>
            <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
          </td>
          <td class="px-5 py-4 text-gray-500 text-xs">
            {{ $order->shipping_city ?? '—' }}
            @if($order->shipping_country) <span class="ml-1">· {{ $order->shipping_country }}</span> @endif
          </td>
          <td class="px-5 py-4">
            @if($order->tracking_number)
              <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $order->tracking_number }}</span>
              @if($order->carrier) <p class="text-xs text-gray-400 mt-0.5">{{ $order->carrier }}</p> @endif
            @else
              <span class="text-gray-300 text-xs">—</span>
            @endif
          </td>
          <td class="px-5 py-4">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
              {{ match($order->shipping_status) {
                  'delivered'        => 'bg-green-100 text-green-700',
                  'shipped'          => 'bg-blue-100 text-blue-700',
                  'out_for_delivery' => 'bg-indigo-100 text-indigo-700',
                  'processing'       => 'bg-amber-100 text-amber-700',
                  'failed'           => 'bg-red-100 text-red-700',
                  'cancelled'        => 'bg-gray-200 text-gray-500',
                  default            => 'bg-gray-100 text-gray-500',
              } }}">
              {{ $order->shippingStatusIcon() }} {{ $order->shippingStatusLabel() }}
            </span>
          </td>
          <td class="px-5 py-4 text-right">
            <a href="{{ route('admin.physical.order-detail', $order) }}"
              class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:border-blue-400 px-3 py-1.5 rounded-lg transition">
              📍 Suivi
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucune commande physique.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
