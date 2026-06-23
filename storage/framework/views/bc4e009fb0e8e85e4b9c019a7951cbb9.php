<?php $__env->startSection('title','Retraits – LireX Admin'); ?>
<?php $__env->startSection('page-title','Demandes de Retrait'); ?>
<?php $__env->startSection('page-subtitle','Gestion des paiements aux auteurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl p-5 mb-5 shadow-sm border border-slate-100">
  <form method="GET" class="flex gap-4 items-end">
    <div>
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">Tous</option>
        <option value="pending" <?php if(request('status')==='pending'): echo 'selected'; endif; ?>>En attente</option>
        <option value="processing" <?php if(request('status')==='processing'): echo 'selected'; endif; ?>>En cours</option>
        <option value="completed" <?php if(request('status')==='completed'): echo 'selected'; endif; ?>>Complété</option>
        <option value="rejected" <?php if(request('status')==='rejected'): echo 'selected'; endif; ?>>Rejeté</option>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold">Filtrer</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        <?php $__currentLoopData = ['Auteur','Montant','Méthode','Compte','Solde avant','Statut','Date','Actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase"><?php echo e($h); ?></th>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="border-b border-slate-50 hover:bg-slate-50/50">
        <td class="px-5 py-3">
          <div class="flex items-center gap-3">
            <img src="<?php echo e($w->author->avatar_url); ?>" class="w-8 h-8 rounded-full" alt="">
            <div>
              <p class="font-semibold text-slate-700"><?php echo e($w->author->name); ?></p>
              <p class="text-xs text-slate-400"><?php echo e($w->author->email); ?></p>
            </div>
          </div>
        </td>
        <td class="px-5 py-3 font-bold text-slate-800"><?php echo e(number_format($w->amount,0,',',' ')); ?> XAF</td>
        <td class="px-5 py-3">
          <?php $mc=['mtn_momo'=>['MTN MoMo','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel Money','bg-red-100 text-red-800'],'bank'=>['Virement','bg-blue-100 text-blue-800']]; ?>
          <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e(($mc[$w->method]??['',''])[1]); ?>"><?php echo e(($mc[$w->method]??[$w->method,''])[0]); ?></span>
        </td>
        <td class="px-5 py-3 text-slate-600 font-mono text-xs"><?php echo e($w->account_number); ?></td>
        <td class="px-5 py-3 text-slate-500 text-xs"><?php echo e(number_format($w->balance_before,0,',',' ')); ?> XAF</td>
        <td class="px-5 py-3">
          <?php $sc=['pending'=>'badge-pending','processing'=>'bg-blue-100 text-blue-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium','completed'=>'badge-published','rejected'=>'badge-rejected']; ?>
          <span class="<?php echo e($sc[$w->status] ?? 'badge-draft'); ?>"><?php echo e(ucfirst($w->status)); ?></span>
        </td>
        <td class="px-5 py-3 text-slate-400 text-xs"><?php echo e($w->created_at->format('d/m/Y')); ?></td>
        <td class="px-5 py-3">
          <?php if($w->status === 'pending'): ?>
          <div class="flex gap-2">
            <form method="POST" action="<?php echo e(route('admin.withdrawals.approve',$w)); ?>">
              <?php echo csrf_field(); ?>
              <button class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">Traiter</button>
            </form>
            <button onclick="openRejectW(<?php echo e($w->id); ?>)" class="px-3 py-1 bg-red-50 text-red-600 border border-red-200 text-xs rounded-lg hover:bg-red-100 transition">Rejeter</button>
          </div>
          <?php else: ?>
          <span class="text-slate-300 text-xs">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="8" class="px-5 py-14 text-center text-slate-400">Aucune demande de retrait</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-slate-100"><?php echo e($withdrawals->withQueryString()->links()); ?></div>
</div>

<div id="rejectWModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-4">Rejeter la demande</h3>
    <form method="POST" id="rejectWForm">
      <?php echo csrf_field(); ?>
      <textarea name="reason" rows="3" required placeholder="Raison du rejet…" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('rejectWModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2 text-sm text-slate-600">Annuler</button>
        <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2 text-sm font-semibold">Rejeter</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>function openRejectW(id){ document.getElementById('rejectWForm').action=`/admin/withdrawals/${id}/reject`; document.getElementById('rejectWModal').classList.remove('hidden'); }</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/withdrawals/index.blade.php ENDPATH**/ ?>