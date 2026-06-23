@extends('layouts.admin')
@section('title', 'Suivi commande ' . $order->reference . ' – LireX Admin')

@section('content')
<div class="p-6 space-y-6 max-w-5xl">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <a href="{{ route('admin.physical.orders') }}" class="text-sm text-blue-600 hover:underline">← Commandes physiques</a>
      <h1 class="text-2xl font-bold text-gray-900 mt-1">Commande {{ $order->reference }}</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ $order->book->title ?? '—' }} · {{ $order->user->name }}</p>
    </div>
    <span class="px-3 py-1 rounded-full text-sm font-bold
      {{ match($order->shipping_status) {
          'delivered'        => 'bg-green-100 text-green-700',
          'shipped','out_for_delivery' => 'bg-blue-100 text-blue-700',
          'processing'       => 'bg-amber-100 text-amber-700',
          'failed','cancelled' => 'bg-red-100 text-red-700',
          default            => 'bg-gray-100 text-gray-600',
      } }}">
      {{ $order->shippingStatusIcon() }} {{ $order->shippingStatusLabel() }}
    </span>
  </div>

  @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Colonne gauche : info commande + timeline --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Infos client & livraison --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-4">📋 Informations de livraison</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Destinataire</p>
            <p class="font-semibold text-gray-800">{{ $order->full_name ?: $order->user->name }}</p>
          </div>
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Téléphone</p>
            <p class="font-semibold text-gray-800">{{ $order->shipping_phone ?? '—' }}</p>
          </div>
          <div class="col-span-2">
            <p class="text-gray-400 text-xs uppercase tracking-wide">Adresse</p>
            <p class="font-semibold text-gray-800">
              {{ $order->shipping_address ?? '—' }}, {{ $order->shipping_city ?? '' }}
              @if($order->shipping_country) · {{ $order->shipping_country }} @endif
            </p>
          </div>
          @if($order->tracking_number)
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">N° de suivi</p>
            <p class="font-semibold text-gray-800 font-mono">{{ $order->tracking_number }}</p>
          </div>
          @endif
          @if($order->carrier)
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Transporteur</p>
            <p class="font-semibold text-gray-800">{{ $order->carrier }}</p>
          </div>
          @endif
          @if($order->estimated_delivery_date)
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Livraison estimée</p>
            <p class="font-semibold text-gray-800">{{ $order->estimated_delivery_date->translatedFormat('d M Y') }}</p>
          </div>
          @endif
          @if($order->shipped_at)
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Expédié le</p>
            <p class="font-semibold text-gray-800">{{ $order->shipped_at->translatedFormat('d M Y H:i') }}</p>
          </div>
          @endif
          @if($order->delivered_at)
          <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Livré le</p>
            <p class="font-semibold text-green-700">{{ $order->delivered_at->translatedFormat('d M Y H:i') }}</p>
          </div>
          @endif
        </div>
      </div>

      {{-- Timeline --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-5">📍 Historique de suivi</h2>

        @if($order->trackingEvents->isEmpty())
          <p class="text-gray-400 text-sm text-center py-6">Aucun événement enregistré pour le moment.</p>
        @else
        <div class="relative">
          {{-- Ligne verticale --}}
          <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-100"></div>

          <div class="space-y-6">
            @foreach($order->trackingEvents->sortByDesc('occurred_at') as $event)
            <div class="flex gap-4 relative">
              {{-- Icône statut --}}
              <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 z-10 text-lg
                {{ match($event->status) {
                    'delivered'        => 'bg-green-100',
                    'shipped','out_for_delivery' => 'bg-blue-100',
                    'processing'       => 'bg-amber-100',
                    'failed','cancelled' => 'bg-red-100',
                    default            => 'bg-gray-100',
                } }}">
                {{ \App\Models\OrderTrackingEvent::statusIcon($event->status) }}
              </div>
              <div class="flex-1 pb-2">
                <div class="flex items-center justify-between gap-2">
                  <p class="font-semibold text-gray-800 text-sm">{{ \App\Models\OrderTrackingEvent::statusLabel($event->status) }}</p>
                  <p class="text-xs text-gray-400 flex-shrink-0">{{ $event->occurred_at->translatedFormat('d M Y H:i') }}</p>
                </div>
                @if($event->location)
                  <p class="text-xs text-blue-600 mt-0.5">📍 {{ $event->location }}</p>
                @endif
                <p class="text-sm text-gray-600 mt-1">{{ $event->description }}</p>
                @if($event->creator)
                  <p class="text-xs text-gray-400 mt-0.5">Par {{ $event->creator->name }}</p>
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      </div>

      {{-- Ajouter événement simple --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-4">➕ Ajouter un événement de suivi</h2>
        <form method="POST" action="{{ route('admin.physical.add-event', $order) }}" class="space-y-3">
          @csrf
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500 font-medium">Lieu (optionnel)</label>
              <input name="location" placeholder="Brazzaville — Entrepôt"
                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
            </div>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">Description *</label>
            <input name="description" required placeholder="Ex: Colis arrivé au centre de tri…"
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
          </div>
          <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Enregistrer l'événement
          </button>
        </form>
      </div>
    </div>

    {{-- Colonne droite : mise à jour statut --}}
    <div class="space-y-5">

      {{-- Livre commandé --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-3 text-sm">📚 Livre commandé</h2>
        @if($order->book)
          <div class="flex gap-3">
            <img src="{{ $order->book->cover_url ?? '/img/default-cover.jpg' }}" class="w-12 h-16 object-cover rounded-lg">
            <div>
              <p class="font-semibold text-sm text-gray-800">{{ $order->book->title }}</p>
              <p class="text-xs text-gray-500">{{ $order->book->author->name ?? '' }}</p>
              <p class="text-sm font-bold text-green-600 mt-1">{{ number_format($order->amount, 0, ',', ' ') }} {{ $order->currency }}</p>
            </div>
          </div>
        @endif
      </div>

      {{-- Mettre à jour le statut --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-4">🚚 Mettre à jour le statut</h2>
        <form method="POST" action="{{ route('admin.physical.shipping', $order) }}" class="space-y-3">
          @csrf
          <div>
            <label class="text-xs text-gray-500 font-medium">Nouveau statut *</label>
            <select name="shipping_status" required
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
              <option value="processing"       {{ $order->shipping_status==='processing'      ? 'selected':'' }}>📦 En préparation</option>
              <option value="shipped"          {{ $order->shipping_status==='shipped'         ? 'selected':'' }}>🚚 Expédiée</option>
              <option value="out_for_delivery" {{ $order->shipping_status==='out_for_delivery'? 'selected':'' }}>🏍️ En cours de livraison</option>
              <option value="delivered"        {{ $order->shipping_status==='delivered'       ? 'selected':'' }}>✅ Livrée</option>
              <option value="failed"           {{ $order->shipping_status==='failed'          ? 'selected':'' }}>❌ Échec</option>
              <option value="cancelled"        {{ $order->shipping_status==='cancelled'       ? 'selected':'' }}>🚫 Annulée</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">N° de suivi</label>
            <input name="tracking_number" value="{{ $order->tracking_number }}"
              placeholder="Ex: CGO123456789" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">Transporteur</label>
            <input name="carrier" value="{{ $order->carrier }}"
              placeholder="DHL, La Poste Congo…" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">Date de livraison estimée</label>
            <input name="estimated_delivery_date" type="date" value="{{ $order->estimated_delivery_date?->format('Y-m-d') }}"
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">Lieu de l'événement</label>
            <input name="event_location" placeholder="Ex: Brazzaville — Centre de tri"
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
          </div>
          <div>
            <label class="text-xs text-gray-500 font-medium">Message pour le client *</label>
            <textarea name="event_description" required rows="2"
              placeholder="Ex: Votre colis a été pris en charge par DHL…"
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
          </div>
          <button type="submit"
            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">
            Mettre à jour & notifier
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
