<?php $__env->startSection('title', 'Conversation – LireX Auteur'); ?>
<?php $__env->startSection('page-title', $conversation->subject ?? 'Conversation'); ?>
<?php $__env->startSection('breadcrumb'); ?>
  <a href="<?php echo e(route('author.chat.index')); ?>">Messages</a> › Conversation
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
.msg-bubble-me    { background:#0073bb; color:#fff; border-radius:18px 18px 4px 18px; padding:10px 14px; max-width:340px; font-size:.85rem; line-height:1.45; }
.msg-bubble-other { background:#f0f0f0; color:#16191f; border-radius:18px 18px 18px 4px; padding:10px 14px; max-width:340px; font-size:.85rem; line-height:1.45; }
.msg-bubble-sys   { background:#fef3c7; color:#92400e; border-radius:8px; padding:7px 14px; font-size:.78rem; font-style:italic; text-align:center; max-width:420px; margin:0 auto; }
.chat-input { border:1.5px solid #d5d9d9; border-radius:6px; padding:10px 14px; font-size:.85rem; color:#16191f; outline:none; resize:none; transition:border-color .15s; line-height:1.5; }
.chat-input:focus { border-color:#0073bb; box-shadow:0 0 0 3px rgba(0,115,187,.1); }
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:760px; margin:0 auto;">

  
  <div style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:12px;">
      <?php
        $other = $conversation->participants->first(fn($p) => $p->user_id !== auth()->id());
        $isAdmin = in_array($conversation->type, ['admin_author', 'admin_reader']);
      ?>
      <div style="width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; flex-shrink:0;
                  <?php echo e($isAdmin ? 'background:#dbeafe; color:#1d4ed8;' : 'background:#d1fae5; color:#065f46;'); ?>">
        <?php echo e($other ? strtoupper(substr($other->user->name ?? 'U', 0, 1)) : '?'); ?>

      </div>
      <div>
        <p style="font-weight:700; color:#16191f; font-size:.9rem;"><?php echo e($other->user->name ?? 'Utilisateur'); ?></p>
        <p style="font-size:.75rem; color:#545b64;">
          <?php echo e($isAdmin ? '🛡 Administration LireX' : '📖 Client'); ?>

          <?php if($conversation->book): ?> · <?php echo e($conversation->book->title); ?> <?php endif; ?>
        </p>
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
      <?php if($conversation->status === 'open'): ?>
        <span style="background:#d1fae5; color:#065f46; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px;">● Active</span>
      <?php else: ?>
        <span style="background:#fee2e2; color:#991b1b; font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:10px;">Fermée</span>
      <?php endif; ?>
      <a href="<?php echo e(route('author.chat.index')); ?>" style="font-size:.8rem; color:#0073bb; text-decoration:none;">
        ← Retour
      </a>
    </div>
  </div>

  
  <div id="chat-box" style="background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:18px; min-height:400px; max-height:520px; overflow-y:auto; display:flex; flex-direction:column; gap:14px; margin-bottom:14px;">
    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $isMe = $msg->sender_id === auth()->id(); ?>

      <?php if($msg->type === 'system'): ?>
        <div class="msg-bubble-sys"><?php echo e($msg->body); ?></div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column; align-items:<?php echo e($isMe ? 'flex-end' : 'flex-start'); ?>;">
          <?php if(!$isMe): ?>
            <span style="font-size:.72rem; color:#545b64; margin-bottom:4px; padding-left:4px;"><?php echo e($msg->sender->name); ?></span>
          <?php endif; ?>
          <div class="<?php echo e($isMe ? 'msg-bubble-me' : 'msg-bubble-other'); ?>">
            <?php echo e($msg->body); ?>

          </div>
          <span style="font-size:.68rem; color:#aab7b8; margin-top:4px; padding-<?php echo e($isMe ? 'right' : 'left'); ?>:4px;">
            <?php echo e($msg->created_at->format('d/m H:i')); ?>

            <?php if($isMe && $msg->is_read): ?> <i class="fas fa-check-double" style="color:#4cc9f0; margin-left:3px;"></i> <?php endif; ?>
          </span>
        </div>
      <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($messages->isEmpty()): ?>
      <div style="text-align:center; color:#aab7b8; margin:auto;">
        <i class="far fa-comment-dots" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
        <p style="font-size:.85rem;">Aucun message. Commencez la discussion !</p>
      </div>
    <?php endif; ?>
  </div>

  
  <?php if($conversation->status === 'open'): ?>
    <form method="POST" action="<?php echo e(route('author.chat.message', $conversation)); ?>">
      <?php echo csrf_field(); ?>
      <div style="display:flex; gap:10px; align-items:flex-end;">
        <textarea name="body" rows="2" required placeholder="Votre message..." class="chat-input" style="flex:1;"></textarea>
        <button type="submit" class="btn-primary" style="height:46px; flex-shrink:0;">
          <i class="fas fa-paper-plane"></i> Envoyer
        </button>
      </div>
    </form>
  <?php else: ?>
    <div style="background:#f8f9fa; border:1px solid #d5d9d9; border-radius:6px; padding:14px; text-align:center; color:#545b64; font-size:.85rem;">
      <i class="fas fa-lock" style="margin-right:6px;"></i> Cette conversation est fermée.
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  // Scroll to bottom on load
  const chatBox = document.getElementById('chat-box');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

  // Submit on Ctrl+Enter
  document.querySelector('textarea')?.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') this.closest('form').submit();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/chat/show.blade.php ENDPATH**/ ?>