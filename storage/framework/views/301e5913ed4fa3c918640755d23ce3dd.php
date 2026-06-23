<?php $__env->startSection('title', 'Paiements en attente'); ?>
<?php $__env->startSection('page-title', 'Paiements en attente'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
<div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
  <i class="fa-solid fa-check-circle text-green-500"></i> <?php echo e(session('success')); ?>

</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
  <i class="fa-solid fa-circle-xmark text-red-500"></i> <?php echo e(session('error')); ?>

</div>
<?php endif; ?>

<?php if($pending->isEmpty()): ?>
<div class="flex flex-col items-center justify-center py-24 text-slate-400">
  <i class="fa-solid fa-circle-check text-5xl mb-4 text-green-300"></i>
  <p class="font-semibold text-lg">Aucun paiement en attente</p>
  <p class="text-sm mt-1">Tous les abonnements ont été traités.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
    <h2 class="font-bold text-slate-800">
      <i class="fa-solid fa-clock text-amber-500 mr-2"></i>
      <?php echo e($pending->total()); ?> paiement(s) à valider
    </h2>
  </div>
  <div class="divide-y divide-slate-100">
    <?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $methodIcon  = match($ap->payment_method) { 'mtn_momo'=>'🟡', 'airtel_money'=>'🔴', default=>'💳' };
      $methodLabel = match($ap->payment_method) { 'mtn_momo'=>'MTN MoMo', 'airtel_money'=>'Airtel Money', default=>'Carte' };
    ?>
    <div class="px-6 py-5 flex items-center gap-5">
      
      <img src="<?php echo e($ap->user->avatar_url); ?>" class="w-11 h-11 rounded-xl object-cover flex-shrink-0" alt="">

      
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-semibold text-slate-800"><?php echo e($ap->user->name); ?></span>
          <span class="text-slate-400 text-sm"><?php echo e($ap->user->email); ?></span>
        </div>
        <div class="flex items-center gap-3 mt-1 flex-wrap">
          <span class="text-xs bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-full">
            <?php echo e($ap->plan->name); ?>

          </span>
          <span class="text-xs text-slate-500">
            <?php echo e($ap->billing === 'annual' ? 'Annuel' : 'Mensuel'); ?>

          </span>
          <span class="text-xs font-bold text-amber-700">
            <?php echo e(number_format($ap->amount_paid, 0, ',', ' ')); ?> <?php echo e($ap->currency); ?>

          </span>
          <span class="text-xs text-slate-500">
            <?php echo e($methodIcon); ?> <?php echo e($methodLabel); ?>

          </span>
        </div>
        <?php if($ap->transaction_id): ?>
        <div class="mt-1 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-1.5 inline-block">
          <i class="fa-solid fa-receipt text-slate-400 mr-1"></i>
          <strong>Référence :</strong> <?php echo e($ap->transaction_id); ?>

        </div>
        <?php else: ?>
        <div class="mt-1 text-xs text-slate-400 italic">Aucune référence fournie</div>
        <?php endif; ?>
        <div class="text-xs text-slate-400 mt-1">
          Soumis le <?php echo e($ap->created_at->format('d/m/Y à H:i')); ?>

        </div>
      </div>

      
      <div class="flex flex-col gap-2 flex-shrink-0">
        <form method="POST" action="<?php echo e(route('admin.payments.approve', $ap)); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit"
            onclick="return confirm('Valider le paiement de <?php echo e(addslashes($ap->user->name)); ?> pour le forfait <?php echo e(addslashes($ap->plan->name)); ?> ?')"
            class="w-full px-4 py-2 rounded-xl text-xs font-bold text-white bg-green-500 hover:bg-green-600 transition flex items-center gap-2">
            <i class="fa-solid fa-check"></i> Valider
          </button>
        </form>
        <form method="POST" action="<?php echo e(route('admin.payments.reject', $ap)); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit"
            onclick="return confirm('Rejeter ce paiement ?')"
            class="w-full px-4 py-2 rounded-xl text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 transition flex items-center gap-2">
            <i class="fa-solid fa-xmark"></i> Rejeter
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>

<div class="mt-5"><?php echo e($pending->links()); ?></div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/payments/index.blade.php ENDPATH**/ ?>