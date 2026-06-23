<?php $__env->startSection('title','Commandes – LireX Admin'); ?>
<?php $__env->startSection('page-title','Commandes & Paiements'); ?>
<?php $__env->startSection('page-subtitle','Suivi de toutes les transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Revenus totaux</p>
    <p class="text-2xl font-bold text-green-600"><?php echo e(number_format($summary['total_revenue'],0,',',' ')); ?> XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Revenus aujourd'hui</p>
    <p class="text-2xl font-bold text-blue-600"><?php echo e(number_format($summary['today_revenue'],0,',',' ')); ?> XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-1">Paiements en attente</p>
    <p class="text-2xl font-bold text-amber-600"><?php echo e($summary['pending_count']); ?></p>
  </div>
</div>

<div class="bg-white rounded-2xl p-5 mb-5 shadow-sm border border-slate-100">
  <form method="GET" class="flex gap-4 items-end">
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Tous</option>
        <option value="paid" <?php if(request('status')==='paid'): echo 'selected'; endif; ?>>Payé</option>
        <option value="pending" <?php if(request('status')==='pending'): echo 'selected'; endif; ?>>En attente</option>
        <option value="failed" <?php if(request('status')==='failed'): echo 'selected'; endif; ?>>Échoué</option>
        <option value="refunded" <?php if(request('status')==='refunded'): echo 'selected'; endif; ?>>Remboursé</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Méthode</label>
      <select name="method" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Toutes</option>
        <option value="mtn_momo">MTN MoMo</option>
        <option value="airtel_money">Airtel Money</option>
        <option value="stripe">Stripe</option>
        <option value="free">Gratuit</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        <?php $__currentLoopData = ['Référence','Livre','Acheteur','Montant','Méthode','Statut','Date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider"><?php echo e($h); ?></th>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="border-b border-slate-50 hover:bg-slate-50/50">
        <td class="px-5 py-3 font-mono text-blue-600 text-xs font-semibold"><?php echo e($order->reference); ?></td>
        <td class="px-5 py-3">
          <div class="flex items-center gap-2">
            <img src="<?php echo e($order->book->cover_url); ?>" class="w-8 h-10 object-cover rounded" alt="">
            <div>
              <p class="font-semibold text-slate-700 max-w-[140px] truncate text-xs"><?php echo e($order->book->title); ?></p>
              <p class="text-slate-400 text-xs"><?php echo e($order->book->author->name); ?></p>
            </div>
          </div>
        </td>
        <td class="px-5 py-3 text-slate-600"><?php echo e($order->user->name); ?></td>
        <td class="px-5 py-3 font-semibold text-slate-800"><?php echo e(number_format($order->amount,0,',',' ')); ?> <?php echo e($order->currency); ?></td>
        <td class="px-5 py-3">
          <?php $m=['mtn_momo'=>['MTN','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel','bg-red-100 text-red-800'],'stripe'=>['Carte','bg-blue-100 text-blue-800'],'free'=>['Gratuit','bg-green-100 text-green-800']]; ?>
          <?php if(isset($m[$order->payment_method])): ?>
          <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($m[$order->payment_method][1]); ?>"><?php echo e($m[$order->payment_method][0]); ?></span>
          <?php endif; ?>
        </td>
        <td class="px-5 py-3">
          <?php $s=['paid'=>'badge-published','pending'=>'badge-pending','failed'=>'badge-rejected','refunded'=>'badge-draft']; ?>
          <span class="<?php echo e($s[$order->payment_status] ?? 'badge-draft'); ?>"><?php echo e(ucfirst($order->payment_status)); ?></span>
        </td>
        <td class="px-5 py-3 text-slate-400 text-xs"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="7" class="px-5 py-14 text-center text-slate-400">Aucune commande</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-slate-100"><?php echo e($orders->withQueryString()->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>