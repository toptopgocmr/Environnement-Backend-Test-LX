@extends('layouts.admin')

@section('page-title', 'Rapport d\'analyse IA')
@section('page-subtitle', $aiReview->book->title ?? '')

@section('content')
<div class="max-w-3xl">

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
    <div class="flex items-center gap-4 mb-6">
      <img src="{{ $aiReview->book->cover_url ?? '/img/default-cover.jpg' }}" class="w-16 h-20 object-cover rounded-lg">
      <div>
        <p class="font-bold text-slate-800 text-lg">{{ $aiReview->book->title }}</p>
        <p class="text-slate-400 text-sm">{{ $aiReview->book->author->name ?? '—' }} · {{ $aiReview->book->category->name ?? '' }}</p>
      </div>
    </div>

    {{-- Scores --}}
    <div class="grid grid-cols-5 gap-3 mb-6">
      @foreach(['score_overall'=>'Global','score_originality'=>'Originalité','score_structure'=>'Structure','score_language'=>'Langue','score_norms'=>'Normes'] as $key => $label)
        @php $val = $aiReview->$key; @endphp
        <div class="text-center p-3 rounded-xl {{ $val >= 70 ? 'bg-green-50' : ($val >= 40 ? 'bg-amber-50' : 'bg-red-50') }}">
          <p class="text-2xl font-bold {{ $val >= 70 ? 'text-green-600' : ($val >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $val ?? '—' }}</p>
          <p class="text-xs text-slate-500 mt-1">{{ $label }}</p>
        </div>
      @endforeach
    </div>

    <div class="mb-4">
      <p class="text-slate-400 text-sm mb-1">Résumé de l'analyse</p>
      <p class="text-slate-700 bg-slate-50 rounded-lg p-4 text-sm">{{ $aiReview->summary }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <p class="text-slate-400 text-sm mb-2">⚠️ Problèmes détectés</p>
        <ul class="space-y-1 text-sm">
          @forelse($aiReview->issues ?? [] as $issue)
            <li class="flex gap-2 text-slate-700"><span class="text-amber-500">•</span>{{ $issue }}</li>
          @empty
            <li class="text-slate-400">Aucun problème majeur détecté</li>
          @endforelse
        </ul>
      </div>
      <div>
        <p class="text-slate-400 text-sm mb-2">💡 Suggestions</p>
        <ul class="space-y-1 text-sm">
          @forelse($aiReview->suggestions ?? [] as $suggestion)
            <li class="flex gap-2 text-slate-700"><span class="text-blue-500">•</span>{{ $suggestion }}</li>
          @empty
            <li class="text-slate-400">Aucune suggestion</li>
          @endforelse
        </ul>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-4 text-sm border-t border-slate-100 pt-4">
      <div>
        <p class="text-slate-400">ISBN valide</p>
        <p class="font-medium">{{ $aiReview->isbn_valid === null ? 'N/A' : ($aiReview->isbn_valid ? 'Oui ✓' : 'Non ✗') }}</p>
      </div>
      <div>
        <p class="text-slate-400">Langue détectée</p>
        <p class="font-medium">{{ $aiReview->detected_language ?? '—' }}</p>
      </div>
      <div>
        <p class="text-slate-400">Type détecté</p>
        <p class="font-medium">{{ $aiReview->detected_document_type ?? '—' }}</p>
      </div>
    </div>

    @if($aiReview->plagiarism_flag)
    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      <i class="fa-solid fa-triangle-exclamation"></i> Plagiat potentiel détecté : {{ $aiReview->plagiarism_score }}% de similarité
    </div>
    @endif
  </div>

  {{-- Actions --}}
  <div class="flex gap-3">
    <form method="POST" action="{{ route('admin.books.ai-analyze', $aiReview->book) }}">
      @csrf
      <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm font-medium hover:bg-slate-800">
        <i class="fa-solid fa-rotate"></i> Relancer l'analyse
      </button>
    </form>

    @if($aiReview->book->status === 'pending')
    <form method="POST" action="{{ route('admin.books.approve-ai', $aiReview->book) }}" class="flex-1 flex gap-2">
      @csrf
      <input type="text" name="note" class="flex-1 border border-slate-200 rounded-lg px-3 text-sm" placeholder="Note d'approbation (optionnelle)">
      <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
        <i class="fa-solid fa-check"></i> Approuver et publier
      </button>
    </form>
    @endif

    <a href="{{ route('admin.books.show', $aiReview->book) }}" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50">
      Voir la fiche livre
    </a>
  </div>
</div>
@endsection
