<?php $__env->startSection('page-title', 'Conversation'); ?>
<?php $__env->startSection('page-subtitle', $conversation->subject ?? ''); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-4">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
      <div>
        <?php $__currentLoopData = $conversation->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php if($p->user_id !== auth()->id()): ?>
            <p class="font-semibold text-slate-800"><?php echo e($p->user->name); ?></p>
            <p class="text-slate-400 text-xs"><?php echo e($p->user->email); ?> · <?php echo e(ucfirst($p->user->role)); ?></p>
          <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php if($conversation->status === 'open'): ?>
      <form method="POST" action="<?php echo e(route('admin.chat.close', $conversation)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="text-sm text-red-600 hover:underline">Fermer la conversation</button>
      </form>
      <?php else: ?>
        <span class="badge-rejected">Fermée</span>
      <?php endif; ?>
    </div>

    <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto">
      <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $isAdmin = $msg->sender_id === auth()->id(); ?>
        <div class="flex <?php echo e($isAdmin ? 'justify-end' : 'justify-start'); ?>">
          <div class="max-w-xs">
            <?php if($msg->type === 'system'): ?>
              <p class="text-center text-xs text-slate-400 italic"><?php echo e($msg->body); ?></p>
            <?php else: ?>
              <div class="rounded-2xl px-4 py-2.5 text-sm <?php echo e($isAdmin ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-slate-100 text-slate-800 rounded-bl-sm'); ?>">
                <?php echo e($msg->body); ?>

              </div>
              <p class="text-xs text-slate-400 mt-1 <?php echo e($isAdmin ? 'text-right' : 'text-left'); ?>"><?php echo e($msg->created_at->format('H:i')); ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($conversation->status === 'open'): ?>
    <form method="POST" action="<?php echo e(route('admin.chat.message', $conversation)); ?>" class="p-4 border-t border-slate-100 flex gap-2">
      <?php echo csrf_field(); ?>
      <input type="text" name="body" required class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Votre message...">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Envoyer</button>
    </form>
    <?php endif; ?>
  </div>

  <a href="<?php echo e(route('admin.chat.index')); ?>" class="text-blue-600 text-sm hover:underline">← Retour aux conversations</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/chat/show.blade.php ENDPATH**/ ?>