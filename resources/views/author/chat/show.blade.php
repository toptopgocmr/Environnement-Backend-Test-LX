@extends('layouts.author')

@section('title', 'Conversation – LireX Auteur')
@section('page-title', $conversation->subject ?? 'Conversation')
@section('breadcrumb')
  <a href="{{ route('author.chat.index') }}">Messages</a> › Conversation
@endsection

@section('styles')
.msg-bubble-me    { background:#0073bb; color:#fff; border-radius:18px 18px 4px 18px; padding:10px 14px; max-width:340px; font-size:.85rem; line-height:1.45; }
.msg-bubble-other { background:#f0f0f0; color:#16191f; border-radius:18px 18px 18px 4px; padding:10px 14px; max-width:340px; font-size:.85rem; line-height:1.45; }
.msg-bubble-sys   { background:#fef3c7; color:#92400e; border-radius:8px; padding:7px 14px; font-size:.78rem; font-style:italic; text-align:center; max-width:420px; margin:0 auto; }
.chat-input { border:1.5px solid #d5d9d9; border-radius:6px; padding:10px 14px; font-size:.85rem; color:#16191f; outline:none; resize:none; transition:border-color .15s; line-height:1.5; }
.chat-input:focus { border-color:#0073bb; box-shadow:0 0 0 3px rgba(0,115,187,.1); }
@endsection

@section('content')
<div style="max-width:760px; margin:0 auto;">

  {{-- Info conversation --}}
  <div style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:12px;">
      @php
        $other = $conversation->participants->first(fn($p) => $p->user_id !== auth()->id());
        $isAdmin = in_array($conversation->type, ['admin_author', 'admin_reader']);
      @endphp
      <div style="width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0;
                  {{ $isAdmin ? 'background:#dbeafe; color:#1d4ed8;' : 'background:#d1fae5; color:#065f46;' }}">
        {{ $other ? strtoupper(substr($other->user->name ?? 'U', 0, 1)) : '?' }}
      </div>
      <div>
        <p style="font-weight:700; color:#16191f; font-size:.9rem;">{{ $other->user->name ?? 'Utilisateur' }}</p>
        <p style="font-size:.75rem; color:#545b64;">
          {{ $isAdmin ? '🛡 Administration LireX' : '📖 Client' }}
          @if($conversation->book) · {{ $conversation->book->title }} @endif
        </p>
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
      @if($conversation->status === 'open')
        <span style="background:#d1fae5; color:#065f46; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px;">● Active</span>
      @else
        <span style="background:#fee2e2; color:#991b1b; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px;">Fermée</span>
      @endif
      <a href="{{ route('author.chat.index') }}" style="font-size:.8rem; color:#0073bb; text-decoration:none;">
        ← Retour
      </a>
    </div>
  </div>

  {{-- Zone messages --}}
  <div id="chat-box" style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:18px; min-height:400px; max-height:520px; overflow-y:auto; display:flex; flex-direction:column; gap:14px; margin-bottom:14px;">
    @foreach($messages as $msg)
      @php $isMe = $msg->sender_id === auth()->id(); @endphp

      @if($msg->type === 'system')
        <div class="msg-bubble-sys">{{ $msg->body }}</div>
      @else
        <div style="display:flex; flex-direction:column; align-items:{{ $isMe ? 'flex-end' : 'flex-start' }};">
          @if(!$isMe)
            <span style="font-size:.72rem; color:#545b64; margin-bottom:4px; padding-left:4px;">{{ $msg->sender->name }}</span>
          @endif
          <div class="{{ $isMe ? 'msg-bubble-me' : 'msg-bubble-other' }}">
            {{ $msg->body }}
          </div>
          <span style="font-size:.68rem; color:#aab7b8; margin-top:4px; padding-{{ $isMe ? 'right' : 'left' }}:4px;">
            {{ $msg->created_at->format('d/m H:i') }}
            @if($isMe && $msg->is_read) <i class="fas fa-check-double" style="color:#4cc9f0; margin-left:3px;"></i> @endif
          </span>
        </div>
      @endif
    @endforeach

    @if($messages->isEmpty())
      <div style="text-align:center; color:#aab7b8; margin:auto;">
        <i class="far fa-comment-dots" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        <p style="font-size:.85rem;">Aucun message. Commencez la discussion !</p>
      </div>
    @endif
  </div>

  {{-- Zone saisie --}}
  @if($conversation->status === 'open')
    <form method="POST" action="{{ route('author.chat.message', $conversation) }}">
      @csrf
      <div style="display:flex; gap:10px; align-items:flex-end;">
        <textarea name="body" rows="2" required placeholder="Votre message..." class="chat-input" style="flex:1;"></textarea>
        <button type="submit" class="btn-primary" style="height:46px; flex-shrink:0;">
          <i class="fas fa-paper-plane"></i> Envoyer
        </button>
      </div>
    </form>
  @else
    <div style="background:#f8f9fa; border:1px solid #d5d9d9; border-radius:6px; padding:14px; text-align:center; color:#545b64; font-size:.85rem;">
      <i class="fas fa-lock" style="margin-right:6px;"></i> Cette conversation est fermée.
    </div>
  @endif

</div>
@endsection

@section('scripts')
<script>
  // Scroll to bottom on load
  const chatBox = document.getElementById('chat-box');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

  // Submit on Ctrl+Enter
  document.querySelector('textarea')?.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') this.closest('form').submit();
  });
</script>
@endsection
