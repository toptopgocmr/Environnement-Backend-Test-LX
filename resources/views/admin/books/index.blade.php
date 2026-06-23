@extends('layouts.admin')
@section('title','Livres – LireX Admin')
@section('page-title','Gestion des Livres')
@section('page-subtitle','Modération et suivi de tous les ouvrages')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-2xl p-5 mb-6 shadow-sm border border-slate-100">
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[180px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Recherche</label>
      <input name="search" value="{{ request('search') }}" placeholder="Titre, auteur…"
        class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>
    <div class="min-w-[140px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Tous</option>
        @foreach(['pending'=>'En attente','published'=>'Publié','rejected'=>'Rejeté','draft'=>'Brouillon','suspended'=>'Suspendu'] as $v=>$l)
          <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="min-w-[160px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Catégorie</label>
      <select name="category_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Toutes</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    <a href="{{ route('admin.books.index') }}" class="text-slate-400 text-sm hover:text-slate-600 py-2">Réinitialiser</a>
  </form>
</div>

{{-- Stats row --}}
@php
  $statuses = ['pending'=>['En attente','amber'], 'published'=>['Publiés','green'], 'rejected'=>['Rejetés','red'], 'draft'=>['Brouillons','slate']];
@endphp
<div class="grid grid-cols-4 gap-4 mb-6">
  @foreach($statuses as $st=>[$label,$color])
  <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center gap-3">
    <div class="w-9 h-9 bg-{{ $color }}-100 rounded-lg flex items-center justify-center">
      <div class="w-2.5 h-2.5 rounded-full bg-{{ $color }}-500"></div>
    </div>
    <div>
      <p class="font-bold text-slate-800">{{ \App\Models\Book::where('status',$st)->count() }}</p>
      <p class="text-xs text-slate-400">{{ $label }}</p>
    </div>
  </div>
  @endforeach
</div>

{{-- Books table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          @foreach(['Livre','Auteur','Catégorie','Prix','Statut','Ventes','Actions'] as $h)
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $h }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($books as $book)
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <img src="{{ $book->cover_url }}" class="w-10 h-14 object-cover rounded-lg shadow-sm" alt="">
              <div>
                <p class="font-semibold text-slate-800 max-w-[180px] truncate">{{ $book->title }}</p>
                <p class="text-xs text-slate-400">{{ strtoupper($book->language) }} · {{ strtoupper($book->format) }}</p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <img src="{{ $book->author->avatar_url }}" class="w-7 h-7 rounded-full object-cover" alt="">
              <span class="text-slate-600">{{ $book->author->name }}</span>
            </div>
          </td>
          <td class="px-5 py-3 text-slate-500">{{ $book->category?->name ?? '—' }}</td>
          <td class="px-5 py-3 font-semibold text-slate-800">{{ $book->price_formatted }}</td>
          <td class="px-5 py-3">
            @php $badges=['pending'=>'badge-pending','published'=>'badge-published','rejected'=>'badge-rejected','draft'=>'badge-draft','suspended'=>'bg-orange-100 text-orange-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']; @endphp
            <span class="{{ $badges[$book->status] ?? 'badge-draft' }}">{{ ucfirst($book->status) }}</span>
          </td>
          <td class="px-5 py-3 text-slate-600">{{ $book->orders()->where('payment_status','paid')->count() }}</td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <a href="{{ route('admin.books.show',$book) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Voir"><i class="fa-solid fa-eye text-sm"></i></a>
              @if($book->status==='pending')
              <form method="POST" action="{{ route('admin.books.approve',$book) }}" class="inline">
                @csrf
                <button class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Approuver"><i class="fa-solid fa-check text-sm"></i></button>
              </form>
              @endif
              <form method="POST" action="{{ route('admin.books.featured',$book) }}" class="inline">
                @csrf
                <button class="p-1.5 {{ $book->is_featured?'text-amber-500':'text-slate-400' }} hover:bg-amber-50 rounded-lg transition" title="{{ $book->is_featured?'Retirer':'Mettre en avant' }}">
                  <i class="fa-{{ $book->is_featured?'solid':'regular' }} fa-star text-sm"></i>
                </button>
              </form>
              <form method="POST" action="{{ route('admin.books.destroy',$book) }}" class="inline" onsubmit="return confirm('Supprimer ce livre ?')">
                @csrf @method('DELETE')
                <button class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Supprimer"><i class="fa-solid fa-trash text-sm"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-5 py-16 text-center text-slate-400"><i class="fa-solid fa-book-open text-3xl mb-3 block opacity-30"></i>Aucun livre trouvé</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="px-5 py-4 border-t border-slate-100">{{ $books->withQueryString()->links() }}</div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-4">Rejeter le livre</h3>
    <form method="POST" id="rejectForm">
      @csrf
      <label class="text-sm font-semibold text-slate-600 mb-2 block">Raison du rejet</label>
      <textarea name="reason" rows="4" required placeholder="Expliquez pourquoi ce livre est rejeté…" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
      <div class="flex gap-3 mt-4">
        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2 text-sm font-semibold hover:bg-red-700">Rejeter</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openReject(bookId) {
  document.getElementById('rejectForm').action = `/admin/books/${bookId}/reject`;
  document.getElementById('rejectModal').classList.remove('hidden');
}
</script>
@endpush
