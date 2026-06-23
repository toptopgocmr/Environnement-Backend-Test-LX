<?php $__env->startSection('title','Revenus & Retraits'); ?>
<?php $__env->startSection('page-title','Revenus & Retraits'); ?>
<?php $__env->startSection('page-subtitle','Suivez vos gains et gérez vos retraits'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-3 gap-5 mb-8">
  <div class="bg-gradient-to-br from-[#0A1628] to-[#1D4ED8] rounded-2xl p-6 text-white shadow-xl col-span-1">
    <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Solde disponible</p>
    <p class="text-3xl font-bold mb-5"><?php echo e(number_format($summary['pending_balance'],0,',',' ')); ?> <span class="text-lg">XAF</span></p>
    <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="w-full bg-white text-blue-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-blue-50 transition">
      <i class="fa-solid fa-money-bill-transfer mr-1"></i> Retirer mes gains
    </button>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-2">Total gagné (payé)</p>
    <p class="text-2xl font-bold text-green-600"><?php echo e(number_format($summary['total_earned'],0,',',' ')); ?> XAF</p>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <p class="text-xs text-slate-400 mb-2">Total retiré</p>
    <p class="text-2xl font-bold text-slate-700"><?php echo e(number_format($summary['total_withdrawn'],0,',',' ')); ?> XAF</p>
  </div>
</div>

<div class="grid grid-cols-2 gap-6">
  
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Historique des royalties</h3>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Livre</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Montant brut</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Ma part</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $royalties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-t border-slate-50 hover:bg-slate-50/50">
          <td class="px-5 py-3 text-slate-700 text-xs max-w-[120px] truncate"><?php echo e($r->order->book->title ?? '—'); ?></td>
          <td class="px-5 py-3 text-slate-600 text-xs"><?php echo e(number_format($r->gross_amount,0,',',' ')); ?> XAF</td>
          <td class="px-5 py-3 font-bold text-green-600 text-xs"><?php echo e(number_format($r->net_amount,0,',',' ')); ?> XAF</td>
          <td class="px-5 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($r->status==='paid'?'bg-green-100 text-green-700':'bg-amber-100 text-amber-700'); ?>"><?php echo e(ucfirst($r->status)); ?></span>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Aucune royalty pour l'instant</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-slate-100"><?php echo e($royalties->links()); ?></div>
  </div>

  
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Demandes de retrait</h3>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Montant</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Méthode</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Statut</th>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-t border-slate-50 hover:bg-slate-50/50">
          <td class="px-5 py-3 font-bold text-slate-800 text-xs"><?php echo e(number_format($w->amount,0,',',' ')); ?> XAF</td>
          <td class="px-5 py-3 text-slate-500 text-xs"><?php echo e(strtoupper(str_replace('_',' ',$w->method))); ?></td>
          <td class="px-5 py-3">
            <?php $sc=['pending'=>'bg-amber-100 text-amber-700','completed'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','processing'=>'bg-blue-100 text-blue-700']; ?>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($sc[$w->status]??'bg-slate-100 text-slate-600'); ?>"><?php echo e(ucfirst($w->status)); ?></span>
          </td>
          <td class="px-5 py-3 text-slate-400 text-xs"><?php echo e($w->created_at->format('d/m/Y')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Aucune demande</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-slate-100"><?php echo e($withdrawals->links()); ?></div>
  </div>
</div>


<div id="withdrawModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-1">Demander un retrait</h3>
    <p class="text-slate-400 text-sm mb-5">Solde disponible : <strong class="text-green-600"><?php echo e(number_format($summary['pending_balance'],0,',',' ')); ?> XAF</strong></p>
    <form method="POST" action="<?php echo e(route('author.earnings.withdraw')); ?>">
      <?php echo csrf_field(); ?>
      <div class="space-y-4">
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Montant (min. 5 000 XAF)</label>
          <input type="number" name="amount" min="5000" max="<?php echo e($summary['pending_balance']); ?>" required
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex : 25 000"/>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Méthode</label>
          <select name="method" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            <option value="mtn_momo">📱 MTN Mobile Money</option>
            <option value="airtel_money">📲 Airtel Money</option>
            <option value="bank">🏦 Virement bancaire</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Numéro / Compte</label>
          <input type="text" name="account_number" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none" placeholder="+242 06 XXX XX XX"/>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-600 mb-1.5 block">Nom du bénéficiaire</label>
          <input type="text" name="account_name" required value="<?php echo e(Auth::user()->name); ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none"/>
        </div>
      </div>
      <div class="flex gap-3 mt-6">
        <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700">Demander le retrait</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/earnings/index.blade.php ENDPATH**/ ?>