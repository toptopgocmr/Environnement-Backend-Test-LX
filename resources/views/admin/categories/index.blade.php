@extends('layouts.admin')
@section('title', 'Catégories')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Catégories</h1>
            <p class="text-slate-400 text-sm mt-1">Gérez les catégories de la bibliothèque LireX</p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#2563EB">
            + Nouvelle catégorie
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-900/30 border border-green-700 text-green-300 rounded-xl p-4 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($categories as $cat)
            <div class="rounded-2xl p-5 flex items-start justify-between" style="background:#162035;border:1px solid #1E3A6A">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background:{{ $cat->color ?? '#2563EB' }}20">
                        {{ $cat->icon ?? '📚' }}
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">{{ $cat->name }}</h3>
                        <p class="text-slate-400 text-xs mt-0.5">{{ $cat->books_count ?? 0 }} ouvrages</p>
                        @if($cat->description)
                            <p class="text-slate-500 text-xs mt-1 line-clamp-2">{{ $cat->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cat->is_active ? 'bg-green-900/40 text-green-400' : 'bg-slate-700 text-slate-400' }}">
                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="flex gap-1 mt-1">
                        <button onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}', '{{ $cat->icon ?? '' }}', '{{ $cat->color ?? '' }}', {{ $cat->is_active ? 'true' : 'false' }})"
                            class="p-1.5 rounded-lg hover:bg-blue-900/30 text-blue-400 transition">✏️</button>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                            onsubmit="return confirm('Supprimer cette catégorie ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-red-400 transition">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 text-slate-500">
                <div class="text-4xl mb-3">📁</div>
                <p>Aucune catégorie créée</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Create --}}
<div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.6)">
    <div class="w-full max-w-lg rounded-2xl p-6" style="background:#0F2044;border:1px solid #1E3A6A">
        <h2 class="text-white font-bold text-xl mb-5">Nouvelle catégorie</h2>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            @include('admin.categories._form')
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#2563EB">Créer</button>
                <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-slate-400" style="background:#162035">Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.6)">
    <div class="w-full max-w-lg rounded-2xl p-6" style="background:#0F2044;border:1px solid #1E3A6A">
        <h2 class="text-white font-bold text-xl mb-5">Modifier la catégorie</h2>
        <form id="form-edit" method="POST" class="space-y-4">
            @csrf @method('PUT')
            @include('admin.categories._form', ['editing' => true])
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#2563EB">Enregistrer</button>
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-slate-400" style="background:#162035">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, description, icon, color, isActive) {
    const form = document.getElementById('form-edit');
    form.action = '/admin/categories/' + id;
    form.querySelector('[name=name]').value = name;
    form.querySelector('[name=description]').value = description;
    form.querySelector('[name=icon]').value = icon;
    form.querySelector('[name=color]').value = color;
    form.querySelector('[name=is_active]').checked = isActive;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
