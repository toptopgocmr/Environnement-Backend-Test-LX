@extends('layouts.author')

@section('title', 'Messages – LireX Auteur')
@section('page-title', 'Messages')
@section('page-subtitle', 'Vos conversations avec l\'administration et vos clients')

@section('page-actions')
  {{-- Bouton contacter l'admin --}}
  <form method="POST" action="{{ route('author.chat.start-admin') }}" style="display:inline;">
    @csrf
    <input type="hidden" name="subject" value="Question à l'administration">
    <button type="submit" class="btn-orange">
      <i class="fas fa-headset"></i> Contacter l'administration
    </button>
  </form>
@endsection

@section('content')
{{-- Onglets --}}
<div style="display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #e5e7eb; padding-bottom:0;">
  @php
    $tabs = [
      'all'     => ['label' => 'Toutes',       'icon' => 'fa-comments'],
      'admin'   => ['label' => 'Admin ↔ Moi',  'icon' => 'fa-headset'],
      'clients' => ['label' => 'Mes Clients',  'icon' => 'fa-users'],
    ];
  @endphp
  @foreach($tabs as $key => $tab)
    <a href="{{ route('author.chat.index', ['type' => $key]) }}"
       style="display:flex; align-items:center; gap:6px; padding:8px 16px; font-size:.82rem; font-weight:600; text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .15s;
              {{ $type === $key ? 'color:#0073bb; border-bottom-color:#0073bb;' : 'color:#545b64;' }}">
      <i class="fas {{ $tab['icon'] }}" style="font-size:.77rem;"></i>
      {{ $tab['label'] }}
    </a>
  @endforeach
</div>

{{-- Liste des conversations --}}
<div style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; overflow:hidden;">
  @forelse($conversations as $conv)
    @php
      $other = $conv->participants->first(fn($p) => $p->user_id !== auth()->id());
      $isAdmin = in_array($conv->type, ['admin_author','admin_reader']);
    @endphp
    <a href="{{ route('author.chat.show', $conv) }}"
       style="display:flex; align-items:center; gap:14px; padding:14px 18px; border-bottom:1px solid #f0f0f0; text-decoration:none; transition:background .1s; {{ $conv->unread_count > 0 ? 'background:#fffbeb;' : '' }}"
       onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='{{ $conv->unread_count > 0 ? '#fffbeb' : '' }}'">

      {{-- Avatar --}}
      <div style="width:42px; height:42px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem;
                  {{ $isAdmin ? 'background:#dbeafe; color:#1d4ed8;' : 'background:#d1fae5; color:#065f46;' }}">
        {{ $other ? strtoupper(substr($other->user->name ?? 'U', 0, 1)) : '?' }}
      </div>

      <div style="flex:1; min-width:0;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;">
          <span style="font-size:.85rem; font-weight:{{ $conv->unread_count > 0 ? '700' : '600' }}; color:#16191f;">
            {{ $other->user->name ?? 'Utilisateur supprimé' }}
          </span>
          @if($isAdmin)
            <span style="background:#dbeafe; color:#1d4ed8; font-size:.65rem; font-weight:700; padding:1px 6px; border-radius:10px;">Admin</span>
          @else
            <span style="background:#d1fae5; color:#065f46; font-size:.65rem; font-weight:700; padding:1px 6px; border-radius:10px;">Client</span>
          @endif
          @if($conv->status === 'closed')
            <span style="background:#fee2e2; color:#991b1b; font-size:.65rem; font-weight:700; padding:1px 6px; border-radius:10px;">Fermée</span>
          @endif
        </div>
        @if($conv->book)
          <p style="font-size:.73rem; color:#0073bb; margin-bottom:2px;">📖 {{ $conv->book->title }}</p>
        @endif
        <p style="font-size:.78rem; color:#545b64; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:{{ $conv->unread_count > 0 ? '600' : '400' }};">
          {{ $conv->lastMessage->body ?? 'Aucun message' }}
        </p>
      </div>

      <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px; flex-shrink:0;">
        <span style="font-size:.72rem; color:#aab7b8;">{{ $conv->last_message_at?->diffForHumans() }}</span>
        @if($conv->unread_count > 0)
          <span style="background:#ef4444; color:#fff; font-size:.65rem; font-weight:800; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center;">
            {{ $conv->unread_count > 9 ? '9+' : $conv->unread_count }}
          </span>
        @endif
      </div>
    </a>
  @empty
    <div style="padding:48px; text-align:center; color:#aab7b8;">
      <i class="far fa-comment-dots" style="font-size:2.5rem; display:block; margin-bottom:12px; opacity:.4;"></i>
      <p style="font-size:.9rem; font-weight:600; color:#545b64; margin-bottom:6px;">Aucune conversation</p>
      <p style="font-size:.8rem;">
        @if($type === 'admin')
          Cliquez sur "Contacter l'administration" pour démarrer une discussion.
        @elseif($type === 'clients')
          Vos clients peuvent vous contacter depuis la fiche de vos livres.
        @else
          Vous n'avez pas encore de messages.
        @endif
      </p>
    </div>
  @endforelse
</div>

@if($conversations->hasPages())
  <div style="margin-top:16px;">{{ $conversations->withQueryString()->links() }}</div>
@endif
@endsection
