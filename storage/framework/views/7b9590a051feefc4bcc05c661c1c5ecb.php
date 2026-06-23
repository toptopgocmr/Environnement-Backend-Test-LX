<?php $__env->startSection('page-title', 'Messagerie'); ?>
<?php $__env->startSection('page-subtitle', 'Conversations entre lecteurs, auteurs et administration'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex gap-3 mb-6">
  <?php $currentType = request('type'); ?>
  <a href="<?php echo e(route('admin.chat.index')); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e(!$currentType ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">Toutes</a>
  <a href="<?php echo e(route('admin.chat.index', ['type' => 'reader_author'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($currentType === 'reader_author' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">Lecteur ↔ Auteur</a>
  <a href="<?php echo e(route('admin.chat.index', ['type' => 'admin_author'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($currentType === 'admin_author' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">Admin ↔ Auteur</a>
  <a href="<?php echo e(route('admin.chat.index', ['type' => 'admin_reader'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($currentType === 'admin_reader' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">Admin ↔ Lecteur</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-100">
  <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
      $other = $conv->participants->first(fn($p) => $p->user_id !== auth()->id());
    ?>
    <a href="<?php echo e(route('admin.chat.show', $conv)); ?>" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition">
      <img src="<?php echo e($other->user->avatar_url ?? ''); ?>" class="w-10 h-10 rounded-full object-cover">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <p class="font-semibold text-slate-800"><?php echo e($other->user->name ?? 'Utilisateur'); ?></p>
          <span class="badge-draft text-[10px]">
            <?php echo e(['reader_author'=>'Lecteur↔Auteur','admin_author'=>'Admin↔Auteur','admin_reader'=>'Admin↔Lecteur','support'=>'Support'][$conv->type] ?? $conv->type); ?>

          </span>
          <?php if($conv->status === 'closed'): ?>
            <span class="badge-rejected text-[10px]">Fermée</span>
          <?php endif; ?>
        </div>
        <p class="text-slate-500 text-sm truncate"><?php echo e($conv->subject ?? $conv->book->title ?? '—'); ?></p>
        <p class="text-slate-400 text-xs truncate"><?php echo e($conv->lastMessage->body ?? 'Aucun message'); ?></p>
      </div>
      <span class="text-slate-400 text-xs"><?php echo e($conv->last_message_at?->diffForHumans()); ?></span>
    </a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="px-6 py-12 text-center text-slate-400">Aucune conversation.</div>
  <?php endif; ?>
</div>

<div class="mt-6"><?php echo e($conversations->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/chat/index.blade.php ENDPATH**/ ?>