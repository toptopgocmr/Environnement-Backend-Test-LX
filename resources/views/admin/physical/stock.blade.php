@extends('layouts.admin')

@section('page-title', 'Stock physique')
@section('page-subtitle', 'Gestion des exemplaires papier disponibles')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Livre</th>
        <th class="px-6 py-3 text-left">Auteur</th>
        <th class="px-6 py-3 text-left">Prix physique</th>
        <th class="px-6 py-3 text-left">Stock actuel</th>
        <th class="px-6 py-3 text-left">Vendus</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($books as $book)
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4 font-medium text-slate-800 max-w-xs truncate">{{ $book->title }}</td>
        <td class="px-6 py-4 text-slate-600">{{ $book->author->name ?? '—' }}</td>
        <td class="px-6 py-4 text-slate-600">{{ $book->physical_price ? number_format($book->physical_price, 0, ',', ' ') . ' XAF' : '—' }}</td>
        <td class="px-6 py-4">
          <span class="font-bold {{ $book->physical_stock > 10 ? 'text-green-600' : ($book->physical_stock > 0 ? 'text-amber-600' : 'text-red-600') }}">
            {{ $book->physical_stock }}
          </span>
        </td>
        <td class="px-6 py-4 text-slate-600">{{ $book->sold ?? 0 }}</td>
        <td class="px-6 py-4 text-right">
          <button onclick="document.getElementById('stock-{{ $book->id }}').classList.toggle('hidden')" class="text-blue-600 hover:underline font-medium">+ Ajouter du stock</button>
        </td>
      </tr>
      <tr id="stock-{{ $book->id }}" class="hidden">
        <td colspan="6" class="px-6 py-4 bg-slate-50">
          <form method="POST" action="{{ route('admin.physical.add-stock', $book) }}" class="flex gap-3 items-end">
            @csrf
            <div>
              <label class="text-xs text-slate-500">Quantité à ajouter</label>
              <input type="number" name="quantity" min="1" required class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm w-32">
            </div>
            <div>
              <label class="text-xs text-slate-500">Raison</label>
              <input type="text" name="reason" placeholder="Réception fournisseur..." class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm w-64">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium">Ajouter</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucun livre disponible en version physique.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-6">{{ $books->links() }}</div>
@endsection
