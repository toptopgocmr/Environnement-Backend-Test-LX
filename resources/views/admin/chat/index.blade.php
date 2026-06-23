@extends('layouts.admin')

@section('page-title', 'Messagerie')
@section('page-subtitle', 'Conversations entre lecteurs, auteurs et administration')

@section('content')
<div class="flex gap-3 mb-6">
  @php $currentType = request('type'); @endphp
  <a href="{{ route('admin.chat.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ !$currentType ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Toutes</a>
  <a href="{{ route('admin.chat.index', ['type' => 'reader_author']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $currentType === 'reader_author' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Lecteur ↔ Auteur</a>
  <a href="{{ route('admin.chat.index', ['type' => 'admin_author']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $currentType === 'admin_author' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Admin ↔ Auteur</a>
  <a href="{{ route('admin.chat.index', ['type' => 'admin_reader']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $currentType === 'admin_reader' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">Admin ↔ Lecteur</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-100">
  @forelse($conversations as $conv)
    @php
      $other = $conv->participants->first(fn($p) => $p->user_id !== auth()->id());
    @endphp
    <a href="{{ route('admin.chat.show', $conv) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition">
      <img src="{{ $other->user->avatar_url ?? '' }}" class="w-10 h-10 rounded-full object-cover">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <p class="font-semibold text-slate-800">{{ $other->user->name ?? 'Utilisateur' }}</p>
          <span class="badge-draft text-[10px]">
            {{ ['reader_author'=>'Lecteur↔Auteur','admin_author'=>'Admin↔Auteur','admin_reader'=>'Admin↔Lecteur','support'=>'Support'][$conv->type] ?? $conv->type }}
          </span>
          @if($conv->status === 'closed')
            <span class="badge-rejected text-[10px]">Fermée</span>
          @endif
        </div>
        <p class="text-slate-500 text-sm truncate">{{ $conv->subject ?? $conv->book->title ?? '—' }}</p>
        <p class="text-slate-400 text-xs truncate">{{ $conv->lastMessage->body ?? 'Aucun message' }}</p>
      </div>
      <span class="text-slate-400 text-xs">{{ $conv->last_message_at?->diffForHumans() }}</span>
    </a>
  @empty
    <div class="px-6 py-12 text-center text-slate-400">Aucune conversation.</div>
  @endforelse
</div>

<div class="mt-6">{{ $conversations->links() }}</div>
@endsection
