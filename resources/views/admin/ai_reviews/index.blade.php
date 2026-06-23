@extends('layouts.admin')

@section('page-title', 'Analyses IA')
@section('page-subtitle', 'Rapports d\'analyse automatique des contenus soumis')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Livre</th>
        <th class="px-6 py-3 text-left">Auteur</th>
        <th class="px-6 py-3 text-left">Score global</th>
        <th class="px-6 py-3 text-left">Plagiat</th>
        <th class="px-6 py-3 text-left">Recommandation</th>
        <th class="px-6 py-3 text-left">Statut</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($reviews as $review)
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4 font-medium text-slate-800 max-w-xs truncate">{{ $review->book->title ?? '—' }}</td>
        <td class="px-6 py-4 text-slate-600">{{ $review->book->author->name ?? '—' }}</td>
        <td class="px-6 py-4">
          @if($review->score_overall !== null)
            <span class="font-bold {{ $review->score_overall >= 70 ? 'text-green-600' : ($review->score_overall >= 40 ? 'text-amber-600' : 'text-red-600') }}">
              {{ $review->score_overall }}/100
            </span>
          @else
            <span class="text-slate-400">—</span>
          @endif
        </td>
        <td class="px-6 py-4">
          @if($review->plagiarism_flag)
            <span class="badge-rejected">{{ $review->plagiarism_score }}% détecté</span>
          @else
            <span class="badge-published">OK</span>
          @endif
        </td>
        <td class="px-6 py-4">
          @php $recoMap = ['approve'=>['Approuver','badge-published'],'review'=>['À examiner','badge-pending'],'reject'=>['Rejeter','badge-rejected']]; @endphp
          @if($review->recommendation)
            <span class="{{ $recoMap[$review->recommendation][1] }}">{{ $recoMap[$review->recommendation][0] }}</span>
          @else
            <span class="text-slate-400">—</span>
          @endif
        </td>
        <td class="px-6 py-4">
          @php $statusMap = ['pending'=>'badge-draft','processing'=>'badge-pending','completed'=>'badge-published','failed'=>'badge-rejected']; @endphp
          <span class="{{ $statusMap[$review->status] }}">{{ ucfirst($review->status) }}</span>
        </td>
        <td class="px-6 py-4 text-right">
          <a href="{{ route('admin.ai-reviews.show', $review) }}" class="text-blue-600 hover:underline font-medium">Voir le rapport</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Aucune analyse IA pour le moment.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-6">{{ $reviews->links() }}</div>
@endsection
