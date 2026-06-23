<?php $__env->startSection('title','Utilisateurs – LireX Admin'); ?>
<?php $__env->startSection('page-title','Gestion des Utilisateurs'); ?>
<?php $__env->startSection('page-subtitle','Lecteurs, auteurs et administrateurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl p-5 mb-6 shadow-sm border border-slate-100">
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Recherche</label>
      <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Nom, email…"
        class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>
    <div class="min-w-[130px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Rôle</label>
      <select name="role" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none">
        <option value="">Tous</option>
        <option value="reader" <?php if(request('role')==='reader'): echo 'selected'; endif; ?>>Lecteurs</option>
        <option value="author" <?php if(request('role')==='author'): echo 'selected'; endif; ?>>Auteurs</option>
        <option value="admin"  <?php if(request('role')==='admin'): echo 'selected'; endif; ?>>Admins</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    <a href="<?php echo e(route('admin.users.index')); ?>" class="text-slate-400 text-sm hover:text-slate-600 py-2">Reset</a>
  </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <?php $__currentLoopData = ['Utilisateur','Rôle','Inscription','Livres','Statut','Actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider"><?php echo e($h); ?></th>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <img src="<?php echo e($user->avatar_url); ?>" class="w-9 h-9 rounded-full object-cover" alt="">
              <div>
                <div class="flex items-center gap-1.5">
                  <p class="font-semibold text-slate-800"><?php echo e($user->name); ?></p>
                  <?php if($user->is_verified_author): ?><i class="fa-solid fa-circle-check text-blue-500 text-xs"></i><?php endif; ?>
                </div>
                <p class="text-xs text-slate-400"><?php echo e($user->email); ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <?php $roleColors=['admin'=>'bg-purple-100 text-purple-700','author'=>'bg-blue-100 text-blue-700','reader'=>'bg-slate-100 text-slate-600']; ?>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($roleColors[$user->role] ?? 'bg-slate-100 text-slate-600'); ?>"><?php echo e(ucfirst($user->role)); ?></span>
          </td>
          <td class="px-5 py-3 text-slate-500 text-xs"><?php echo e($user->created_at->format('d/m/Y')); ?></td>
          <td class="px-5 py-3 font-semibold text-slate-700"><?php echo e($user->books()->count()); ?></td>
          <td class="px-5 py-3">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
              <?php echo e($user->is_active ? 'Actif' : 'Suspendu'); ?>

            </span>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <a href="<?php echo e(route('admin.users.show',$user)); ?>" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Voir"><i class="fa-solid fa-eye text-sm"></i></a>
              <?php if(!$user->isAdmin()): ?>
              <form method="POST" action="<?php echo e(route('admin.users.toggle-active',$user)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button class="p-1.5 <?php echo e($user->is_active?'text-red-400 hover:bg-red-50':'text-green-500 hover:bg-green-50'); ?> rounded-lg transition" title="<?php echo e($user->is_active?'Suspendre':'Activer'); ?>">
                  <i class="fa-solid fa-<?php echo e($user->is_active?'ban':'circle-check'); ?> text-sm"></i>
                </button>
              </form>
              <?php if(!$user->is_verified_author && $user->role!=='admin'): ?>
              <form method="POST" action="<?php echo e(route('admin.users.verify-author',$user)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Vérifier auteur"><i class="fa-solid fa-badge-check text-sm"></i></button>
              </form>
              <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400"><i class="fa-solid fa-users text-3xl mb-3 block opacity-30"></i>Aucun utilisateur</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="px-5 py-4 border-t border-slate-100"><?php echo e($users->withQueryString()->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/users/index.blade.php ENDPATH**/ ?>