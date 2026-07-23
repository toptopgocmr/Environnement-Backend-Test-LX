<?php $__env->startSection('title', 'Conversation – LireX Auteur'); ?>
<?php $__env->startSection('page-title', $conversation->subject ?? 'Conversation'); ?>
<?php $__env->startSection('breadcrumb'); ?>
  <a href="<?php echo e(route('author.chat.index')); ?>">Messages</a> › Conversation
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
@keyframes livepulse { 0%,100%{opacity:1} 50%{opacity:.25} }

#chat-wrap  { max-width:860px; margin:0 auto; display:flex; flex-direction:column; height:calc(100vh - 180px); min-height:500px; background:#fff; border:1px solid #d5d9d9; border-radius:6px; overflow:hidden; }

#chat-header { background:#232f3e; color:#fff; padding:10px 18px; display:flex; align-items:center; gap:12px; flex-shrink:0; }
#chat-header .av { width:40px;height:40px;border-radius:50%;background:#37475a;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0; }

#chat-body-wrap { display:flex; flex:1; overflow:hidden; }

#participants { width:165px; flex-shrink:0; background:#f8f8f8; border-right:1px solid #d5d9d9; padding:12px 10px; overflow-y:auto; }
#participants h4 { font-size:.7rem; font-weight:700; color:#545b64; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px; }
.p-item { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #eee; }
.p-item:last-child { border:none; }
.p-av  { width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.75rem;color:#fff;flex-shrink:0; }
.p-name { font-size:.76rem; font-weight:600; color:#16191f; }
.p-role { font-size:.67rem; color:#545b64; }

#msg-zone { flex:1; overflow-y:auto; padding:14px 18px; display:flex; flex-direction:column; gap:0; background:#fafafa; }

.date-sep { text-align:center; margin:14px 0 8px; position:relative; }
.date-sep::before { content:''; position:absolute; top:50%; left:0; right:0; height:1px; background:#d5d9d9; }
.date-sep span { position:relative; background:#fafafa; padding:0 10px; font-size:.72rem; color:#545b64; font-weight:600; }

.msg-row { display:flex; gap:10px; padding:4px 0; }
.msg-row.me { background:rgba(0,115,187,.03); border-radius:4px; }
.msg-row.same-sender .msg-av { visibility:hidden; }
.msg-row.same-sender .msg-header { display:none; }

.msg-av { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;color:#fff;flex-shrink:0;margin-top:2px; }
.msg-content { flex:1; min-width:0; }
.msg-header  { display:flex; align-items:baseline; gap:8px; margin-bottom:3px; }
.msg-sender  { font-size:.8rem; font-weight:700; }
.msg-time    { font-size:.68rem; color:#aab7b8; }
.msg-me-badge{ font-size:.62rem; background:#0073bb; color:#fff; padding:1px 5px; border-radius:3px; margin-left:2px; }
.msg-bubble  { display:inline-block; background:#fff; border:1px solid #e3e7e9; border-radius:0 8px 8px 8px; padding:8px 12px; font-size:.86rem; line-height:1.5; color:#16191f; max-width:620px; word-break:break-word; box-shadow:0 1px 2px rgba(0,0,0,.05); }
.msg-row.me .msg-bubble { background:#e8f5fd; border-color:#b8d9f0; border-radius:8px 0 8px 8px; }
.msg-system { text-align:center; font-size:.76rem; font-style:italic; color:#545b64; padding:6px 0; }

#chat-footer { background:#fff; border-top:1px solid #d5d9d9; padding:10px 14px; display:flex; align-items:flex-end; gap:10px; flex-shrink:0; }
#chat-input { flex:1; border:1.5px solid #d5d9d9; border-radius:6px; padding:9px 13px; font-size:.86rem; color:#16191f; outline:none; resize:none; max-height:120px; line-height:1.5; font-family:inherit; transition:border-color .15s; }
#chat-input:focus { border-color:#0073bb; box-shadow:0 0 0 3px rgba(0,115,187,.1); }
#send-btn { height:40px; padding:0 18px; background:#0073bb; color:#fff; border:none; border-radius:6px; font-weight:700; font-size:.85rem; cursor:pointer; flex-shrink:0; transition:background .15s; }
#send-btn:hover { background:#005e9e; }
#chat-hint { font-size:.7rem; color:#aab7b8; padding:2px 14px 4px; background:#fff; display:flex; justify-content:space-between; border-top:1px solid #f0f0f0; flex-shrink:0; }
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $me      = auth()->id();
  $palette = ['#0073bb','#d13212','#1d8102','#8a2be2','#c25100','#00748c'];
  $colors  = [];
  $ci      = 0;
  foreach ($conversation->participants as $p) {
      $colors[$p->user_id] = $palette[$ci++ % count($palette)];
  }
  $other    = $conversation->participants->first(fn($p) => $p->user_id !== $me);
  $isAdmin  = in_array($conversation->type, ['admin_author', 'admin_reader']);
?>

<div id="chat-wrap">

  
  <div id="chat-header">
    <div class="av"><?php echo e(strtoupper(substr($other?->user->name ?? 'U', 0, 1))); ?></div>
    <div style="flex:1;">
      <div style="font-weight:700;font-size:.9rem;"><?php echo e($other?->user->name ?? 'Utilisateur'); ?></div>
      <div style="font-size:.72rem;opacity:.7;">
        <?php echo e($isAdmin ? '🛡 Administration LireX' : '📖 Client'); ?>

        <?php if($conversation->book): ?> · <?php echo e($conversation->book->title); ?><?php endif; ?>
      </div>
    </div>
    <?php if($conversation->status === 'open'): ?>
      <span style="font-size:.72rem;background:rgba(255,255,255,.12);padding:3px 10px;border-radius:10px;display:flex;align-items:center;gap:5px;">
        <span style="width:7px;height:7px;border-radius:50%;background:#1d8102;display:inline-block;animation:livepulse 1.5s infinite;"></span> En direct
      </span>
    <?php else: ?>
      <span style="font-size:.72rem;background:rgba(255,255,255,.12);padding:3px 10px;border-radius:10px;">Fermée</span>
    <?php endif; ?>
    <a href="<?php echo e(route('author.chat.index')); ?>" style="color:rgba(255,255,255,.7);font-size:.8rem;text-decoration:none;margin-left:10px;">← Retour</a>
  </div>

  <div id="chat-body-wrap">

    
    <div id="participants">
      <h4>Participants</h4>
      <?php $__currentLoopData = $conversation->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="p-item">
          <div class="p-av" style="background:<?php echo e($colors[$p->user_id] ?? '#545b64'); ?>">
            <?php echo e(strtoupper(substr($p->user->name ?? 'U', 0, 1))); ?>

          </div>
          <div>
            <div class="p-name"><?php echo e($p->user->name ?? '?'); ?> <?php if($p->user_id === $me): ?><span style="font-size:.62rem;color:#0073bb;">(moi)</span><?php endif; ?></div>
            <div class="p-role"><?php echo e(ucfirst($p->user->role ?? '')); ?></div>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div id="msg-zone">
      <?php $prevSender = null; $prevDate = null; ?>

      <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $isMe       = $msg->sender_id === $me;
          $msgDate    = $msg->created_at->format('Y-m-d');
          $sameSender = ($msg->sender_id === $prevSender);
          $color      = $colors[$msg->sender_id] ?? '#545b64';
          $ini        = strtoupper(substr($msg->sender->name ?? 'U', 0, 1));
        ?>

        <?php if($msgDate !== $prevDate): ?>
          <div class="date-sep">
            <span>
              <?php
                $d = $msg->created_at;
                if($d->isToday())         echo 'Aujourd\'hui';
                elseif($d->isYesterday()) echo 'Hier';
                else                       echo $d->translatedFormat('d F Y');
              ?>
            </span>
          </div>
          <?php $prevDate = $msgDate; $prevSender = null; ?>
        <?php endif; ?>

        <?php if($msg->type === 'system'): ?>
          <div class="msg-system">— <?php echo e($msg->body); ?> —</div>
          <?php $prevSender = null; ?>
        <?php else: ?>
          <div class="msg-row <?php echo e($isMe ? 'me' : ''); ?> <?php echo e($sameSender ? 'same-sender' : ''); ?>" data-msg-id="<?php echo e($msg->id); ?>">
            <div class="msg-av" style="background:<?php echo e($color); ?>"><?php echo e($ini); ?></div>
            <div class="msg-content">
              <div class="msg-header">
                <span class="msg-sender" style="color:<?php echo e($color); ?>"><?php echo e($msg->sender->name ?? 'Inconnu'); ?></span>
                <?php if($isMe): ?><span class="msg-me-badge">Vous</span><?php endif; ?>
                <span class="msg-time"><?php echo e($msg->created_at->format('H:i')); ?></span>
                <?php if($isMe && $msg->is_read): ?><span style="font-size:.68rem;color:#0073bb;">✓✓ Lu</span><?php endif; ?>
              </div>
              <div class="msg-bubble"><?php echo e($msg->body); ?></div>
            </div>
          </div>
          <?php $prevSender = $msg->sender_id; ?>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <?php if($messages->isEmpty()): ?>
        <div style="text-align:center;margin:auto;color:#aab7b8;padding:40px 0;">
          <i class="far fa-comment-dots" style="font-size:2.5rem;display:block;margin-bottom:10px;"></i>
          <p style="font-size:.85rem;">Aucun message. Commencez la discussion !</p>
        </div>
      <?php endif; ?>
    </div>

  </div>

  
  <?php if($conversation->status === 'open'): ?>
  <div id="chat-footer">
    <textarea id="chat-input" rows="1" placeholder="Tapez un message… (Entrée pour envoyer)"></textarea>
    <button id="send-btn"><i class="fas fa-paper-plane"></i> Envoyer</button>
  </div>
  <div id="chat-hint">
    <span id="sending-label" style="display:none;color:#0073bb;">⏳ Envoi…</span>
    <span></span>
    <span id="poll-status"></span>
  </div>
  <?php else: ?>
    <div style="background:#f8f9fa;padding:12px;text-align:center;color:#545b64;font-size:.85rem;flex-shrink:0;">
      <i class="fas fa-lock" style="margin-right:6px;"></i> Conversation fermée.
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function() {
  const ME_ID    = <?php echo e(auth()->id()); ?>;
  const POLL_URL = '<?php echo e(route("author.chat.poll", $conversation)); ?>';
  const SEND_URL = '<?php echo e(route("author.chat.message", $conversation)); ?>';
  const CSRF     = '<?php echo e(csrf_token()); ?>';
  const COLORS   = <?php echo json_encode($colors, 15, 512) ?>;
  const NAMES    = <?php echo json_encode($conversation->participants->pluck('user.name', 'user_id'), 512) ?>;

  const zone    = document.getElementById('msg-zone');
  const input   = document.getElementById('chat-input');
  const sendBtn = document.getElementById('send-btn');
  const sendLbl = document.getElementById('sending-label');
  const pollEl  = document.getElementById('poll-status');

  function playPing() {
    try {
      const ctx=new(window.AudioContext||window.webkitAudioContext)();
      const o=ctx.createOscillator(),g=ctx.createGain();
      o.connect(g);g.connect(ctx.destination);
      o.type='sine';
      o.frequency.setValueAtTime(900,ctx.currentTime);
      o.frequency.exponentialRampToValueAtTime(500,ctx.currentTime+.12);
      g.gain.setValueAtTime(.3,ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+.4);
      o.start();o.stop(ctx.currentTime+.4);
    }catch(e){}
  }

  let lastId = 0, lastSenderId = null;
  document.querySelectorAll('[data-msg-id]').forEach(el => lastId = Math.max(lastId, +el.dataset.msgId));

  function scrollBottom(force) {
    const atBottom = zone.scrollHeight - zone.scrollTop - zone.clientHeight < 180;
    if (force || atBottom) zone.scrollTop = zone.scrollHeight;
  }
  scrollBottom(true);

  function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function buildRow(msg) {
    const isMe  = msg.sender_id === ME_ID;
    const color = COLORS[msg.sender_id] || '#545b64';
    const name  = NAMES[msg.sender_id]  || msg.sender || 'Inconnu';
    const same  = msg.sender_id === lastSenderId;
    lastSenderId = msg.sender_id;

    if (msg.type === 'system') {
      const d = document.createElement('div');
      d.className = 'msg-system';
      d.textContent = `— ${msg.body} —`;
      lastSenderId = null;
      return d;
    }

    const row = document.createElement('div');
    row.className = `msg-row${isMe?' me':''}${same?' same-sender':''}`;
    row.dataset.msgId = msg.id;

    const avStyle = same ? 'visibility:hidden' : '';
    const hdr = same ? '' : `
      <div class="msg-header">
        <span class="msg-sender" style="color:${color}">${esc(name)}</span>
        ${isMe ? '<span class="msg-me-badge">Vous</span>' : ''}
        <span class="msg-time">${msg.created_at}</span>
      </div>`;

    row.innerHTML = `
      <div class="msg-av" style="background:${color};${avStyle}">${name.charAt(0).toUpperCase()}</div>
      <div class="msg-content">${hdr}<div class="msg-bubble">${esc(msg.body)}</div></div>`;
    return row;
  }

  async function poll() {
    try {
      const res = await fetch(`${POLL_URL}?last_id=${lastId}`, {headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
      if (!res.ok) return;
      const { messages } = await res.json();
      if (messages && messages.length) {
        const fromOther = messages.some(m => m.sender_id !== ME_ID);
        messages.forEach(m => { zone.appendChild(buildRow(m)); lastId = Math.max(lastId, m.id); });
        scrollBottom(false);
        if (fromOther) playPing();
        const t = new Date();
        if (pollEl) pollEl.textContent = `Actualisé ${t.getHours()}:${String(t.getMinutes()).padStart(2,'0')}`;
      }
    } catch(e){}
  }

  setInterval(poll, 3000);
  window.addEventListener('beforeunload', () => clearInterval());

  async function sendMsg() {
    const txt = input.value.trim();
    if (!txt) return;
    input.value = ''; autoResize();
    if (sendLbl) sendLbl.style.display = 'inline';
    try {
      const res = await fetch(SEND_URL, {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({ body: txt }),
      });
      if (res.ok) {
        const msg = await res.json();
        zone.appendChild(buildRow(msg));
        lastId = Math.max(lastId, msg.id);
        scrollBottom(true);
      } else { input.value = txt; }
    } catch(e) { input.value = txt; }
    finally { if(sendLbl) sendLbl.style.display='none'; input.focus(); }
  }

  if (sendBtn) sendBtn.addEventListener('click', sendMsg);
  if (input) {
    input.addEventListener('keydown', e => { if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMsg();} });
    input.addEventListener('input', autoResize);
    input.focus();
  }

  function autoResize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';
  }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/chat/show.blade.php ENDPATH**/ ?>