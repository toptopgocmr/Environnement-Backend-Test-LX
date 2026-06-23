<?php $__env->startSection('page-title', 'Demandes de compte'); ?>
<?php $__env->startSection('page-subtitle', 'Activation des comptes auteur, auditeur et institution'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex gap-3 mb-6">
  <?php $current = request('status'); ?>
  <a href="<?php echo e(route('admin.accounts.index')); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e(!$current ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">
    Toutes
  </a>
  <a href="<?php echo e(route('admin.accounts.index', ['status' => 'pending'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($current === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">
    En attente <span class="ml-1">(<?php echo e($counts['pending']); ?>)</span>
  </a>
  <a href="<?php echo e(route('admin.accounts.index', ['status' => 'approved'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($current === 'approved' ? 'bg-green-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">
    Approuvées <span class="ml-1">(<?php echo e($counts['approved']); ?>)</span>
  </a>
  <a href="<?php echo e(route('admin.accounts.index', ['status' => 'rejected'])); ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo e($current === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 border border-slate-200'); ?>">
    Rejetées <span class="ml-1">(<?php echo e($counts['rejected']); ?>)</span>
  </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Demandeur</th>
        <th class="px-6 py-3 text-left">Type</th>
        <th class="px-6 py-3 text-left">Motivation</th>
        <th class="px-6 py-3 text-left">Statut</th>
        <th class="px-6 py-3 text-left">Date</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4">
          <p class="font-medium text-slate-800"><?php echo e($req->user->name); ?></p>
          <p class="text-slate-400 text-xs"><?php echo e($req->user->email); ?></p>
        </td>
        <td class="px-6 py-4">
          <span class="badge-draft"><?php echo e(['author'=>'Auteur','auditor'=>'Auditeur','institution'=>'Institution'][$req->type] ?? $req->type); ?></span>
        </td>
        <td class="px-6 py-4 max-w-xs truncate text-slate-600"><?php echo e($req->motivation); ?></td>
        <td class="px-6 py-4">
          <?php if($req->status === 'pending'): ?> <span class="badge-pending">En attente</span>
          <?php elseif($req->status === 'approved'): ?> <span class="badge-published">Approuvée</span>
          <?php else: ?> <span class="badge-rejected">Rejetée</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-4 text-slate-400"><?php echo e($req->created_at->format('d/m/Y')); ?></td>
        <td class="px-6 py-4 text-right">
          <a href="<?php echo e(route('admin.accounts.show', $req)); ?>" class="text-blue-600 hover:underline font-medium">Examiner</a>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucune demande.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-6"><?php echo e($requests->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/accounts/index.blade.php ENDPATH**/ ?>