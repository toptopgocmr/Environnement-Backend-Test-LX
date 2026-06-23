@extends('layouts.author')
@section('title', 'Modifier – ' . $book->title)

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('author.books.index') }}" class="p-2 rounded-xl hover:bg-slate-700/30 text-slate-400 transition">←</a>
        <div>
            <h1 class="text-2xl font-bold text-white">Modifier l'ouvrage</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ $book->title }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-900/30 border border-red-700 text-red-300 rounded-xl p-4 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Status badge --}}
    @php
        $statusMap = ['draft'=>['Brouillon','slate'],'pending'=>['En attente','yellow'],'published'=>['Publié','green'],'rejected'=>['Rejeté','red'],'suspended'=>['Suspendu','orange']];
        [$statusLabel, $statusColor] = $statusMap[$book->status] ?? ['Inconnu','slate'];
    @endphp
    <div class="flex items-center gap-3 p-4 rounded-xl" style="background:#162035;border:1px solid #1E3A6A">
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $statusColor }}-900/40 text-{{ $statusColor }}-400">
            {{ $statusLabel }}
        </span>
        @if($book->rejection_reason)
            <p class="text-red-400 text-sm">Raison du rejet : {{ $book->rejection_reason }}</p>
        @endif
    </div>

    <form action="{{ route('author.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- Infos principales --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold flex items-center gap-2">📝 Informations principales</h2>
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Titre de l'ouvrage *</label>
                <input type="text" name="title" value="{{ old('title', $book->title) }}" required
                    class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    style="background:#0F2044;border:1px solid #1E3A6A">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Catégorie *</label>
                    <select name="category_id" required
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $book->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Langue *</label>
                    <select name="language" class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                        @foreach(['Français'=>'fr','English'=>'en','Lingala'=>'ln','Kikongo'=>'kg','Munukutuba'=>'mk'] as $label => $code)
                            <option value="{{ $code }}" {{ $book->language === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Description / Résumé *</label>
                <textarea name="description" rows="5" required
                    class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    style="background:#0F2044;border:1px solid #1E3A6A">{{ old('description', $book->description) }}</textarea>
            </div>
        </div>

        {{-- Prix --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold flex items-center gap-2">💰 Prix & Accès</h2>
            <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer" style="background:#0F2044;border:1px solid #1E3A6A">
                <input type="checkbox" name="is_free" value="1" id="is_free_chk" {{ $book->is_free ? 'checked' : '' }}
                    class="w-4 h-4 rounded" onchange="togglePrice(this)">
                <span class="text-white text-sm font-medium">📖 Accès gratuit</span>
                <span class="text-slate-400 text-xs">(lecture libre sans paiement)</span>
            </label>
            <div id="price_fields" class="{{ $book->is_free ? 'hidden' : '' }} grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Prix (FCFA)</label>
                    <input type="number" name="price" value="{{ old('price', $book->price) }}" min="0" step="100"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Format</label>
                    <select name="format" class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                        @foreach(['pdf'=>'PDF','epub'=>'ePub','both'=>'PDF + ePub'] as $val => $label)
                            <option value="{{ $val }}" {{ $book->format === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Fichiers --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold flex items-center gap-2">📁 Fichiers</h2>
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Couverture</label>
                <div class="flex items-center gap-4">
                    @if($book->cover_image)
                        <img src="{{ $book->cover_url }}" alt="Couverture" class="w-16 h-20 rounded-lg object-cover">
                    @endif
                    <input type="file" name="cover_image" accept="image/*"
                        class="text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-900/40 file:text-blue-300">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2">Fichier PDF / ePub</label>
                <div class="flex items-center gap-4">
                    @if($book->file_path)
                        <span class="text-green-400 text-sm">✅ Fichier actuel présent</span>
                    @endif
                    <input type="file" name="book_file" accept=".pdf,.epub"
                        class="text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-900/40 file:text-blue-300">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" name="action" value="save"
                class="flex-1 py-3 rounded-xl font-semibold text-white text-sm hover:opacity-90 transition-all"
                style="background:linear-gradient(135deg,#1D4ED8,#2563EB)">
                Enregistrer les modifications
            </button>
            @if(in_array($book->status, ['draft','rejected']))
                <button type="submit" name="action" value="submit"
                    class="px-6 py-3 rounded-xl font-semibold text-white text-sm hover:opacity-90 transition-all"
                    style="background:#059669">
                    Soumettre pour validation
                </button>
            @endif
        </div>
    </form>
</div>

<script>
function togglePrice(chk) {
    document.getElementById('price_fields').classList.toggle('hidden', chk.checked);
}
</script>
@endsection
