<?php $__env->startSection('title', 'Commandes physiques – LireX Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">

  
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">📦 Commandes physiques</h1>
      <p class="text-gray-500 text-sm mt-1">Suivi des livraisons de livres papier</p>
    </div>
    
    <div class="flex gap-2">
      <?php $__currentLoopData = [''=>'Toutes','processing'=>'Préparation','shipped'=>'Expédiées','out_for_delivery'=>'En livraison','delivered'=>'Livrées','failed'=>'Échec']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(request()->fullUrlWithQuery(['status'=>$val])); ?>"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition
          <?php echo e(request('status')===$val ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'); ?>">
          <?php echo e($label); ?>

        </a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>

  
  <div class="grid grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-amber-600"><?php echo e($summary['pending']); ?></p>
      <p class="text-gray-500 text-sm mt-1">À préparer</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-blue-600"><?php echo e($summary['processing']); ?></p>
      <p class="text-gray-500 text-sm mt-1">En préparation</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-purple-600"><?php echo e($summary['shipped']); ?></p>
      <p class="text-gray-500 text-sm mt-1">En transit</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
      <p class="text-2xl font-bold text-green-600"><?php echo e($summary['delivered']); ?></p>
      <p class="text-gray-500 text-sm mt-1">Livrées</p>
    </div>
  </div>

  <?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">✅ <?php echo e(session('success')); ?></div>
  <?php endif; ?>

  
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b border-gray-100">
        <tr>
          <th class="px-5 py-3 text-left">Référence</th>
          <th class="px-5 py-3 text-left">Livre</th>
          <th class="px-5 py-3 text-left">Client</th>
          <th class="px-5 py-3 text-left">Destination</th>
          <th class="px-5 py-3 text-left">Suivi</th>
          <th class="px-5 py-3 text-left">Statut</th>
          <th class="px-5 py-3 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-4 font-mono text-gray-600 text-xs"><?php echo e($order->reference); ?></td>
          <td class="px-5 py-4 font-medium text-gray-800 max-w-[150px] truncate"><?php echo e($order->book->title ?? '—'); ?></td>
          <td class="px-5 py-4 text-gray-600">
            <p><?php echo e($order->full_name ?: $order->user->name); ?></p>
            <p class="text-xs text-gray-400"><?php echo e($order->user->email); ?></p>
          </td>
          <td class="px-5 py-4 text-gray-500 text-xs">
            <?php echo e($order->shipping_city ?? '—'); ?>

            <?php if($order->shipping_country): ?> <span class="ml-1">· <?php echo e($order->shipping_country); ?></span> <?php endif; ?>
          </td>
          <td class="px-5 py-4">
            <?php if($order->tracking_number): ?>
              <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded"><?php echo e($order->tracking_number); ?></span>
              <?php if($order->carrier): ?> <p class="text-xs text-gray-400 mt-0.5"><?php echo e($order->carrier); ?></p> <?php endif; ?>
            <?php else: ?>
              <span class="text-gray-300 text-xs">—</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-4">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
              <?php echo e(match($order->shipping_status) {
                  'delivered'        => 'bg-green-100 text-green-700',
                  'shipped'          => 'bg-blue-100 text-blue-700',
                  'out_for_delivery' => 'bg-indigo-100 text-indigo-700',
                  'processing'       => 'bg-amber-100 text-amber-700',
                  'failed'           => 'bg-red-100 text-red-700',
                  'cancelled'        => 'bg-gray-200 text-gray-500',
                  default            => 'bg-gray-100 text-gray-500',
              }); ?>">
              <?php echo e($order->shippingStatusIcon()); ?> <?php echo e($order->shippingStatusLabel()); ?>

            </span>
          </td>
          <td class="px-5 py-4 text-right">
            <a href="<?php echo e(route('admin.physical.order-detail', $order)); ?>"
              class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:border-blue-400 px-3 py-1.5 rounded-lg transition">
              📍 Suivi
            </a>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucune commande physique.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4"><?php echo e($orders->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/physical/orders.blade.php ENDPATH**/ ?>