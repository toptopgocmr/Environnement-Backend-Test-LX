<?php $__env->startSection('title','Profil utilisateur – LireX Admin'); ?>
<?php $__env->startSection('page-title', $user->name); ?>
<?php $__env->startSection('page-subtitle', $user->email); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-3 gap-6">

  
  <div class="col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="text-center mb-4">
      <img src="<?php echo e($user->avatar_url); ?>" class="w-20 h-20 rounded-full object-cover mx-auto ring-2 ring-blue-600">
      <p class="font-bold text-slate-800 mt-3"><?php echo e($user->name); ?></p>
      <p class="text-slate-400 text-sm"><?php echo e($user->email); ?></p>
      <span class="badge-draft mt-2 inline-block"><?php echo e(ucfirst($user->role)); ?></span>
      <?php if(!$user->is_active): ?>
        <span class="badge-rejected mt-2 inline-block ml-1">Suspendu</span>
      <?php endif; ?>
    </div>

    <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
      <div class="flex justify-between"><span class="text-slate-400">Téléphone</span><span class="font-medium"><?php echo e($user->phone ?? '—'); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">Ville</span><span class="font-medium"><?php echo e($user->city ?? '—'); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">Pays</span><span class="font-medium"><?php echo e($user->country ?? '—'); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">Inscrit le</span><span class="font-medium"><?php echo e($user->created_at->format('d/m/Y')); ?></span></div>
    </div>

    <div class="mt-5 space-y-2">
      <form method="POST" action="<?php echo e(route('admin.users.toggle-active', $user)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium <?php echo e($user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100'); ?>">
          <?php echo e($user->is_active ? 'Suspendre le compte' : 'Réactiver le compte'); ?>

        </button>
      </form>

      <?php if($user->role !== 'author' || !$user->is_verified_author): ?>
      <form method="POST" action="<?php echo e(route('admin.users.verify-author', $user)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">
          Vérifier comme auteur
        </button>
      </form>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('admin.users.chat-author', $user)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium bg-slate-50 text-slate-700 hover:bg-slate-100">
          <i class="fa-solid fa-comments"></i> Démarrer une conversation
        </button>
      </form>
    </div>
  </div>

  
  <div class="col-span-2 space-y-6">
    <div class="grid grid-cols-4 gap-4">
      <div class="stat-card text-center"><p class="text-2xl font-bold text-blue-600"><?php echo e($stats['books_count']); ?></p><p class="text-slate-500 text-xs mt-1">Livres</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-green-600"><?php echo e($stats['orders_count']); ?></p><p class="text-slate-500 text-xs mt-1">Achats</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-amber-600"><?php echo e(number_format($stats['total_earnings'], 0, ',', ' ')); ?></p><p class="text-slate-500 text-xs mt-1">Gagné (XAF)</p></div>
      <div class="stat-card text-center"><p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($stats['pending_balance'], 0, ',', ' ')); ?></p><p class="text-slate-500 text-xs mt-1">Solde (XAF)</p></div>
    </div>

    <?php if($user->books->count() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <p class="font-semibold text-slate-800 mb-3">Livres publiés</p>
      <div class="space-y-2">
        <?php $__currentLoopData = $user->books->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50 last:border-0">
            <span class="text-slate-700"><?php echo e($book->title); ?></span>
            <span class="badge-<?php echo e($book->status === 'published' ? 'published' : ($book->status === 'pending' ? 'pending' : 'draft')); ?>"><?php echo e(ucfirst($book->status)); ?></span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if($user->orders->count() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <p class="font-semibold text-slate-800 mb-3">Achats récents</p>
      <div class="space-y-2">
        <?php $__currentLoopData = $user->orders->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50 last:border-0">
            <span class="text-slate-700"><?php echo e($order->book->title ?? '—'); ?></span>
            <span class="text-slate-500"><?php echo e(number_format($order->amount, 0, ',', ' ')); ?> <?php echo e($order->currency); ?></span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/users/show.blade.php ENDPATH**/ ?>