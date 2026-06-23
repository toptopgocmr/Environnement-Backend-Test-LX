<?php $__env->startSection('page-title', 'Commandes physiques'); ?>
<?php $__env->startSection('page-subtitle', 'Suivi des livraisons de livres papier'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-4 gap-4 mb-6">
  <div class="stat-card text-center"><p class="text-2xl font-bold text-amber-600"><?php echo e($summary['pending']); ?></p><p class="text-slate-500 text-sm mt-1">À préparer</p></div>
  <div class="stat-card text-center"><p class="text-2xl font-bold text-blue-600"><?php echo e($summary['processing']); ?></p><p class="text-slate-500 text-sm mt-1">En préparation</p></div>
  <div class="stat-card text-center"><p class="text-2xl font-bold text-purple-600"><?php echo e($summary['shipped']); ?></p><p class="text-slate-500 text-sm mt-1">Expédiées</p></div>
  <div class="stat-card text-center"><p class="text-2xl font-bold text-green-600"><?php echo e($summary['delivered']); ?></p><p class="text-slate-500 text-sm mt-1">Livrées</p></div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Référence</th>
        <th class="px-6 py-3 text-left">Livre</th>
        <th class="px-6 py-3 text-left">Client</th>
        <th class="px-6 py-3 text-left">Adresse</th>
        <th class="px-6 py-3 text-left">Statut</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4 font-mono text-slate-600"><?php echo e($order->reference); ?></td>
        <td class="px-6 py-4 font-medium text-slate-800 max-w-xs truncate"><?php echo e($order->book->title ?? '—'); ?></td>
        <td class="px-6 py-4 text-slate-600"><?php echo e($order->user->name); ?></td>
        <td class="px-6 py-4 text-slate-500 text-xs max-w-[180px] truncate">
          <?php echo e($order->shippingAddress->address_line1 ?? '—'); ?>, <?php echo e($order->shippingAddress->city ?? ''); ?>

        </td>
        <td class="px-6 py-4">
          <?php $statusMap = ['none'=>'badge-draft','processing'=>'badge-pending','shipped'=>'badge-pending','delivered'=>'badge-published']; ?>
          <span class="<?php echo e($statusMap[$order->shipping_status] ?? 'badge-draft'); ?>"><?php echo e(ucfirst($order->shipping_status)); ?></span>
        </td>
        <td class="px-6 py-4 text-right">
          <button onclick="document.getElementById('modal-<?php echo e($order->id); ?>').classList.remove('hidden')" class="text-blue-600 hover:underline font-medium">Mettre à jour</button>
        </td>
      </tr>

      
      <tr id="modal-<?php echo e($order->id); ?>" class="hidden">
        <td colspan="6" class="px-6 py-4 bg-slate-50">
          <form method="POST" action="<?php echo e(route('admin.physical.shipping', $order)); ?>" class="flex gap-3 items-end">
            <?php echo csrf_field(); ?>
            <div>
              <label class="text-xs text-slate-500">Statut</label>
              <select name="shipping_status" class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm">
                <option value="processing">En préparation</option>
                <option value="shipped">Expédiée</option>
                <option value="delivered">Livrée</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-slate-500">N° de suivi</label>
              <input type="text" name="tracking_number" class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm">
            </div>
            <div>
              <label class="text-xs text-slate-500">Transporteur</label>
              <input type="text" name="carrier" placeholder="DHL, La Poste..." class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Mettre à jour</button>
          </form>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucune commande physique.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-6"><?php echo e($orders->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/physical/orders.blade.php ENDPATH**/ ?>