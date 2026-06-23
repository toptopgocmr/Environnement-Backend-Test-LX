<?php $__env->startSection('title', 'Tableau de bord – LireX Admin'); ?>
<?php $__env->startSection('page-title', 'Tableau de bord'); ?>
<?php $__env->startSection('page-subtitle', 'Vue d\'ensemble de la plateforme'); ?>

<?php $__env->startSection('content'); ?>

<div class="grid grid-cols-4 gap-5 mb-8">
  <?php
  $kpis = [
    ['label'=>'Utilisateurs',  'value'=> number_format($stats['total_users']),    'icon'=>'fa-users',        'color'=>'blue',  'sub'=>$stats['new_users_today'].' aujourd\'hui'],
    ['label'=>'Auteurs',       'value'=> number_format($stats['total_authors']),  'icon'=>'fa-pen-nib',      'color'=>'purple','sub'=>'dont '.App\Models\User::where('role','author')->where('is_verified_author',true)->count().' vérifiés'],
    ['label'=>'Livres publiés','value'=> number_format($stats['total_books']),    'icon'=>'fa-book-open',    'color'=>'green', 'sub'=>$stats['pending_books'].' en attente'],
    ['label'=>'Revenus totaux','value'=> number_format($stats['total_revenue'],0,',',' ').' XAF','icon'=>'fa-coins','color'=>'amber','sub'=>number_format($stats['total_orders']).' ventes'],
  ];
  ?>
  <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="stat-card">
    <div class="flex items-start justify-between mb-4">
      <div class="w-11 h-11 bg-<?php echo e($kpi['color']); ?>-100 rounded-xl flex items-center justify-center">
        <i class="fa-solid <?php echo e($kpi['icon']); ?> text-<?php echo e($kpi['color']); ?>-600"></i>
      </div>
      <span class="text-xs text-green-600 bg-green-50 rounded-full px-2 py-0.5 font-medium">↑ actif</span>
    </div>
    <p class="text-2xl font-bold text-slate-800 mb-1"><?php echo e($kpi['value']); ?></p>
    <p class="text-sm font-semibold text-slate-600"><?php echo e($kpi['label']); ?></p>
    <p class="text-xs text-slate-400 mt-1"><?php echo e($kpi['sub']); ?></p>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="grid grid-cols-3 gap-5 mb-8">
  
  <div class="col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Revenus mensuels (XAF)</h3>
      <span class="text-xs text-slate-400">12 derniers mois</span>
    </div>
    <canvas id="revenueChart" height="80"></canvas>
  </div>
  
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">Top Livres</h3>
    <div class="space-y-3">
      <?php $__currentLoopData = $topBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="flex items-center gap-3">
        <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500"><?php echo e($i+1); ?></span>
        <img src="<?php echo e($book->cover_url); ?>" class="w-9 h-12 object-cover rounded" alt="">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-slate-700 truncate"><?php echo e($book->title); ?></p>
          <p class="text-xs text-slate-400"><?php echo e($book->orders_count); ?> ventes</p>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</div>


<div class="grid grid-cols-2 gap-5 mb-8">
  
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Livres en attente de validation</h3>
      <a href="<?php echo e(route('admin.books.index')); ?>?status=pending" class="text-blue-600 text-xs hover:underline">Voir tout</a>
    </div>
    <?php $__empty_1 = true; $__currentLoopData = $pendingBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
      <img src="<?php echo e($book->cover_url); ?>" class="w-10 h-13 object-cover rounded" alt="">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-700 truncate"><?php echo e($book->title); ?></p>
        <p class="text-xs text-slate-400">par <?php echo e($book->author->name); ?></p>
        <p class="text-xs text-slate-400"><?php echo e($book->created_at->diffForHumans()); ?></p>
      </div>
      <div class="flex gap-2">
        <form method="POST" action="<?php echo e(route('admin.books.approve', $book)); ?>">
          <?php echo csrf_field(); ?>
          <button class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">✓</button>
        </form>
        <a href="<?php echo e(route('admin.books.show', $book)); ?>" class="px-3 py-1 bg-slate-100 text-slate-600 text-xs rounded-lg hover:bg-slate-200 transition">Voir</a>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-slate-400 text-sm text-center py-4">Aucun livre en attente 🎉</p>
    <?php endif; ?>
  </div>

  
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Demandes de retrait</h3>
      <a href="<?php echo e(route('admin.withdrawals.index')); ?>" class="text-blue-600 text-xs hover:underline">Voir tout</a>
    </div>
    <?php $__empty_1 = true; $__currentLoopData = $pendingWithdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
      <img src="<?php echo e($w->author->avatar_url); ?>" class="w-9 h-9 rounded-full object-cover" alt="">
      <div class="flex-1">
        <p class="text-sm font-semibold text-slate-700"><?php echo e($w->author->name); ?></p>
        <p class="text-xs text-slate-400"><?php echo e(number_format($w->amount,0,',',' ')); ?> XAF via <?php echo e($w->method); ?></p>
      </div>
      <form method="POST" action="<?php echo e(route('admin.withdrawals.approve', $w)); ?>">
        <?php echo csrf_field(); ?>
        <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 transition">Traiter</button>
      </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-slate-400 text-sm text-center py-4">Aucune demande en attente</p>
    <?php endif; ?>
  </div>
</div>


<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-slate-800">Dernières commandes</h3>
    <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-blue-600 text-xs hover:underline">Voir tout</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
          <th class="pb-3 font-semibold">Référence</th>
          <th class="pb-3 font-semibold">Livre</th>
          <th class="pb-3 font-semibold">Acheteur</th>
          <th class="pb-3 font-semibold">Montant</th>
          <th class="pb-3 font-semibold">Méthode</th>
          <th class="pb-3 font-semibold">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
          <td class="py-3 font-mono text-blue-600 text-xs font-semibold"><?php echo e($order->reference); ?></td>
          <td class="py-3">
            <div class="flex items-center gap-2">
              <img src="<?php echo e($order->book->cover_url); ?>" class="w-8 h-10 object-cover rounded" alt="">
              <span class="truncate max-w-[150px] text-slate-700"><?php echo e($order->book->title); ?></span>
            </div>
          </td>
          <td class="py-3 text-slate-600"><?php echo e($order->user->name); ?></td>
          <td class="py-3 font-semibold text-slate-800"><?php echo e(number_format($order->amount,0,',',' ')); ?> <?php echo e($order->currency); ?></td>
          <td class="py-3">
            <?php $methods=['mtn_momo'=>['MTN MoMo','bg-yellow-100 text-yellow-800'],'airtel_money'=>['Airtel Money','bg-red-100 text-red-800'],'stripe'=>['Carte','bg-blue-100 text-blue-800'],'free'=>['Gratuit','bg-green-100 text-green-800']]; ?>
            <?php if(isset($methods[$order->payment_method])): ?>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($methods[$order->payment_method][1]); ?>"><?php echo e($methods[$order->payment_method][0]); ?></span>
            <?php endif; ?>
          </td>
          <td class="py-3 text-slate-400 text-xs"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($revenueChart->pluck('month'), 15, 512) ?>,
    datasets: [{
      label: 'Revenus (XAF)',
      data: <?php echo json_encode($revenueChart->pluck('total'), 15, 512) ?>,
      borderColor: '#2563EB',
      backgroundColor: 'rgba(37,99,235,0.1)',
      borderWidth: 2.5,
      pointRadius: 4,
      pointBackgroundColor: '#2563EB',
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94A3B8' } },
      y: { grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8' } }
    }
  }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>