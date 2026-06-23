<?php $__env->startSection('page-title', 'Stock physique'); ?>
<?php $__env->startSection('page-subtitle', 'Gestion des exemplaires papier disponibles'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Livre</th>
        <th class="px-6 py-3 text-left">Auteur</th>
        <th class="px-6 py-3 text-left">Prix physique</th>
        <th class="px-6 py-3 text-left">Stock actuel</th>
        <th class="px-6 py-3 text-left">Vendus</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4 font-medium text-slate-800 max-w-xs truncate"><?php echo e($book->title); ?></td>
        <td class="px-6 py-4 text-slate-600"><?php echo e($book->author->name ?? '—'); ?></td>
        <td class="px-6 py-4 text-slate-600"><?php echo e($book->physical_price ? number_format($book->physical_price, 0, ',', ' ') . ' XAF' : '—'); ?></td>
        <td class="px-6 py-4">
          <span class="font-bold <?php echo e($book->physical_stock > 10 ? 'text-green-600' : ($book->physical_stock > 0 ? 'text-amber-600' : 'text-red-600')); ?>">
            <?php echo e($book->physical_stock); ?>

          </span>
        </td>
        <td class="px-6 py-4 text-slate-600"><?php echo e($book->sold ?? 0); ?></td>
        <td class="px-6 py-4 text-right">
          <button onclick="document.getElementById('stock-<?php echo e($book->id); ?>').classList.toggle('hidden')" class="text-blue-600 hover:underline font-medium">+ Ajouter du stock</button>
        </td>
      </tr>
      <tr id="stock-<?php echo e($book->id); ?>" class="hidden">
        <td colspan="6" class="px-6 py-4 bg-slate-50">
          <form method="POST" action="<?php echo e(route('admin.physical.add-stock', $book)); ?>" class="flex gap-3 items-end">
            <?php echo csrf_field(); ?>
            <div>
              <label class="text-xs text-slate-500">Quantité à ajouter</label>
              <input type="number" name="quantity" min="1" required class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm w-32">
            </div>
            <div>
              <label class="text-xs text-slate-500">Raison</label>
              <input type="text" name="reason" placeholder="Réception fournisseur..." class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm w-64">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium">Ajouter</button>
          </form>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucun livre disponible en version physique.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-6"><?php echo e($books->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/physical/stock.blade.php ENDPATH**/ ?>