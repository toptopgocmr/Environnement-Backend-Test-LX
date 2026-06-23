@extends('layouts.admin')

@section('page-title', 'Conversation')
@section('page-subtitle', $conversation->subject ?? '')

@section('content')
<div style="max-width:760px; margin:0 auto;">

  {{-- En-tête conversation --}}
  <div style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:12px;">
      @php
        $other = $conversation->participants->first(fn($p) => $p->user_id !== auth()->id());
      @endphp
      <div style="width:44px; height:44px; border-radius:50%; background:#dbeafe; color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0;">
        {{ $other ? strtoupper(substr($other->user->name ?? 'U', 0, 1)) : '?' }}
      </div>
      <div>
        <p style="font-weight:700; color:#16191f; font-size:.9rem;">{{ $other?->user->name ?? 'Utilisateur' }}</p>
        <p style="font-size:.75rem; color:#545b64;">{{ $other?->user->email }} · {{ ucfirst($other?->user->role ?? '') }}</p>
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
      @if($conversation->status === 'open')
        <span style="background:#d1fae5; color:#065f46; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px; display:flex; align-items:center; gap:5px;">
          <span style="width:7px;height:7px;border-radius:50%;background:#1d8102;display:inline-block;animation:livepulse 1.5s infinite;"></span> Active
        </span>
        <form method="POST" action="{{ route('admin.chat.close', $conversation) }}" style="display:inline;">
          @csrf
          <button type="submit" style="font-size:.8rem; color:#d13212; background:none; border:none; cursor:pointer; padding:0;">Fermer la conversation</button>
        </form>
      @else
        <span style="background:#fee2e2; color:#991b1b; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px;">Fermée</span>
      @endif
      <a href="{{ route('admin.chat.index') }}" style="font-size:.8rem; color:#0073bb; text-decoration:none;">← Retour</a>
    </div>
  </div>

  {{-- Zone messages --}}
  <div id="chat-box" style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:18px; min-height:400px; max-height:520px; overflow-y:auto; display:flex; flex-direction:column; gap:14px; margin-bottom:14px;">
    @foreach($messages as $msg)
      @php $isMe = $msg->sender_id === auth()->id(); @endphp
      <div data-msg-id="{{ $msg->id }}">
        @if($msg->type === 'system')
          <div style="background:#fef3c7; color:#92400e; border-radius:8px; padding:7px 14px; font-size:.78rem; font-style:italic; text-align:center; max-width:420px; margin:0 auto;">{{ $msg->body }}</div>
        @else
          <div style="display:flex; flex-direction:column; align-items:{{ $isMe ? 'flex-end' : 'flex-start' }};">
            @if(!$isMe)
              <span style="font-size:.72rem; color:#545b64; margin-bottom:4px; padding-left:4px;">{{ $msg->sender->name }}</span>
            @endif
            <div style="background:{{ $isMe ? '#0073bb' : '#f0f0f0' }}; color:{{ $isMe ? '#fff' : '#16191f' }}; border-radius:{{ $isMe ? '18px 18px 4px 18px' : '18px 18px 18px 4px' }}; padding:10px 14px; max-width:340px; font-size:.85rem; line-height:1.45;">
              {{ $msg->body }}
            </div>
            <span style="font-size:.68rem; color:#aab7b8; margin-top:4px; padding-{{ $isMe ? 'right' : 'left' }}:4px;">{{ $msg->created_at->format('d/m H:i') }}</span>
          </div>
        @endif
      </div>
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
  <form id="chat-form">
    <div style="display:flex; gap:10px; align-items:flex-end;">
      <textarea id="chat-input" rows="2" required placeholder="Votre message… (Ctrl+Entrée pour envoyer)"
        style="flex:1; border:1.5px solid #d5d9d9; border-radius:6px; padding:10px 14px; font-size:.85rem; color:#16191f; outline:none; resize:none; transition:border-color .15s; line-height:1.5; font-family:inherit;"></textarea>
      <button type="submit" style="height:46px; flex-shrink:0; background:#ff9900; color:#fff; border:none; border-radius:6px; padding:0 18px; font-weight:700; font-size:.85rem; cursor:pointer;">
        <i class="fas fa-paper-plane"></i> Envoyer
      </button>
    </div>
    <div style="font-size:.72rem; color:#aab7b8; margin-top:5px; display:flex; justify-content:space-between;">
      <span id="sending-indicator" style="display:none; color:#0073bb;">⏳ Envoi en cours…</span>
      <span></span>
      <span id="poll-status"></span>
    </div>
  </form>
  @endif

</div>

<style>
@keyframes livepulse { 0%,100%{opacity:1} 50%{opacity:.25} }
</style>

@section('scripts')
<script>
(function() {
  const ME_ID    = {{ auth()->id() }};
  const POLL_URL = '{{ route("admin.chat.poll", $conversation) }}';
  const SEND_URL = '{{ route("admin.chat.message", $conversation) }}';
  const CSRF     = '{{ csrf_token() }}';

  const chatBox   = document.getElementById('chat-box');
  const form      = document.getElementById('chat-form');
  const input     = document.getElementById('chat-input');
  const sendingEl = document.getElementById('sending-indicator');
  const pollEl    = document.getElementById('poll-status');

  // ── Son ping (Web Audio API — aucun fichier externe) ──────────────────
  function playPing() {
    try {
      const ctx  = new (window.AudioContext || window.webkitAudioContext)();
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.15);
      gain.gain.setValueAtTime(0.4, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.45);
    } catch(e) {}
  }

  // ── Dernier ID connu (initialisé depuis PHP) ──────────────────────────
  let lastId = {{ $messages->count() ? $messages->last()->id : 0 }};

  // ── Scroll bas ────────────────────────────────────────────────────────
  function scrollBottom(force) {
    const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 140;
    if (force || atBottom) chatBox.scrollTop = chatBox.scrollHeight;
  }
  scrollBottom(true);

  // ── Echapper HTML ─────────────────────────────────────────────────────
  function esc(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Construire un élément message ────────────────────────────────────
  function buildMsg(msg) {
    const isMe = msg.sender_id === ME_ID;
    const wrap = document.createElement('div');
    wrap.dataset.msgId = msg.id;

    if (msg.type === 'system') {
      wrap.innerHTML = `<div style="background:#fef3c7;color:#92400e;border-radius:8px;padding:7px 14px;font-size:.78rem;font-style:italic;text-align:center;max-width:420px;margin:0 auto;">${esc(msg.body)}</div>`;
    } else {
      const nameRow = !isMe ? `<span style="font-size:.72rem;color:#545b64;margin-bottom:4px;padding-left:4px;">${esc(msg.sender)}</span>` : '';
      const bgColor = isMe ? '#0073bb' : '#f0f0f0';
      const txtColor = isMe ? '#fff' : '#16191f';
      const radius = isMe ? '18px 18px 4px 18px' : '18px 18px 18px 4px';
      const side = isMe ? 'right' : 'left';
      wrap.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:${isMe?'flex-end':'flex-start'};">
          ${nameRow}
          <div style="background:${bgColor};color:${txtColor};border-radius:${radius};padding:10px 14px;max-width:340px;font-size:.85rem;line-height:1.45;">${esc(msg.body)}</div>
          <span style="font-size:.68rem;color:#aab7b8;margin-top:4px;padding-${side}:4px;">${msg.created_at}</span>
        </div>`;
    }
    return wrap;
  }

  // ── Polling toutes les 3 s ────────────────────────────────────────────
  async function poll() {
    try {
      const res = await fetch(`${POLL_URL}?last_id=${lastId}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
      });
      if (!res.ok) return;
      const { messages } = await res.json();
      if (messages && messages.length) {
        const fromOther = messages.some(m => m.sender_id !== ME_ID);
        messages.forEach(m => {
          chatBox.appendChild(buildMsg(m));
          lastId = Math.max(lastId, m.id);
        });
        scrollBottom(false);
        if (fromOther) playPing();
        const t = new Date();
        if (pollEl) pollEl.textContent = `Actualisé à ${t.getHours()}:${String(t.getMinutes()).padStart(2,'0')}`;
      }
    } catch(e) {}
  }

  const pollTimer = setInterval(poll, 3000);
  window.addEventListener('beforeunload', () => clearInterval(pollTimer));

  // ── Envoi AJAX ────────────────────────────────────────────────────────
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const body = input.value.trim();
      if (!body) return;
      input.value = '';
      if (sendingEl) sendingEl.style.display = 'inline';

      try {
        const res = await fetch(SEND_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': CSRF,
          },
          body: JSON.stringify({ body }),
        });
        if (res.ok) {
          const msg = await res.json();
          chatBox.appendChild(buildMsg(msg));
          lastId = Math.max(lastId, msg.id);
          scrollBottom(true);
        } else {
          input.value = body;
          alert('Erreur lors de l\'envoi.');
        }
      } catch(e) {
        input.value = body;
        alert('Erreur réseau.');
      } finally {
        if (sendingEl) sendingEl.style.display = 'none';
        input.focus();
      }
    });

    // Ctrl+Entrée pour envoyer
    input.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') form.requestSubmit();
    });

    // Focus automatique
    input.focus();
  }
})();
</script>
@endsection

@endsection
