<?php $__env->startSection('title', 'Tableau de bord – LireX Auteur'); ?>
<?php $__env->startSection('page-title', 'Mon Tableau de bord'); ?>
<?php $__env->startSection('page-subtitle', 'Bienvenue, ' . Auth::user()->name . ' 👋'); ?>

<?php $__env->startSection('content'); ?>

<div class="grid grid-cols-5 gap-4 mb-8">
  <?php
  $kpis = [
    ['v'=> $stats['published_books'],                  'l'=>'Livres publiés',    'i'=>'fa-book-open',  'c'=>'blue'],
    ['v'=> number_format($stats['total_sales']),        'l'=>'Ventes totales',    'i'=>'fa-cart-check', 'c'=>'green'],
    ['v'=> number_format($stats['pending_balance'],0,',',' ').' XAF', 'l'=>'Solde disponible','i'=>'fa-wallet','c'=>'amber'],
    ['v'=> number_format($stats['total_views']),        'l'=>'Vues totales',      'i'=>'fa-eye',        'c'=>'purple'],
    ['v'=> $stats['followers_count'],                   'l'=>'Abonnés',           'i'=>'fa-heart',      'c'=>'rose'],
  ];
  ?>
  <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
    <div class="w-10 h-10 bg-<?php echo e($k['c']); ?>-100 rounded-xl flex items-center justify-center mb-3">
      <i class="fa-solid <?php echo e($k['i']); ?> text-<?php echo e($k['c']); ?>-600 text-sm"></i>
    </div>
    <p class="text-xl font-bold text-slate-800"><?php echo e($k['v']); ?></p>
    <p class="text-xs text-slate-500 mt-0.5"><?php echo e($k['l']); ?></p>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="grid grid-cols-3 gap-5 mb-8">
  <div class="col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Revenus mensuels (XAF)</h3>
    </div>
    <canvas id="revenueChart" height="100"></canvas>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">Mes Top Livres</h3>
    <div class="space-y-3">
      <?php $__currentLoopData = $topBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route('author.books.stats', $book)); ?>" class="flex items-center gap-3 hover:bg-slate-50 rounded-xl p-2 transition">
        <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600"><?php echo e($i+1); ?></span>
        <img src="<?php echo e($book->cover_url); ?>" class="w-9 h-12 object-cover rounded" alt="">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-slate-700 truncate"><?php echo e($book->title); ?></p>
          <p class="text-xs text-slate-400"><?php echo e($book->orders_count); ?> ventes</p>
        </div>
        <?php if($book->status === 'published'): ?>
          <span class="w-2 h-2 rounded-full bg-green-500"></span>
        <?php elseif($book->status === 'pending'): ?>
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
        <?php endif; ?>
      </a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</div>


<div class="grid grid-cols-3 gap-5">
  
  <div class="bg-gradient-to-br from-[#0A1628] to-[#1D4ED8] rounded-2xl p-6 text-white shadow-xl">
    <p class="text-blue-200 text-xs uppercase tracking-wider mb-2">Solde disponible</p>
    <p class="text-3xl font-bold mb-1"><?php echo e(number_format($stats['pending_balance'],0,',',' ')); ?></p>
    <p class="text-blue-300 text-sm mb-6">XAF</p>
    <div class="mb-4">
      <p class="text-blue-200 text-xs">Total gagné</p>
      <p class="text-xl font-bold"><?php echo e(number_format($stats['total_revenue'],0,',',' ')); ?> XAF</p>
    </div>
    <a href="<?php echo e(route('author.earnings.index')); ?>" class="w-full block text-center bg-white text-blue-600 font-semibold text-sm py-2.5 rounded-xl hover:bg-blue-50 transition">
      <i class="fa-solid fa-money-bill-transfer mr-1"></i> Retirer mes gains
    </a>
  </div>

  
  <div class="col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800">Dernières ventes</h3>
      <a href="<?php echo e(route('author.earnings.index')); ?>" class="text-blue-600 text-xs hover:underline">Voir tout</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-slate-400 border-b border-slate-100">
            <th class="pb-2">Livre</th>
            <th class="pb-2">Acheteur</th>
            <th class="pb-2">Montant</th>
            <th class="pb-2">Ma part</th>
            <th class="pb-2">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $recentSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr class="border-b border-slate-50 hover:bg-slate-50/50">
            <td class="py-2.5">
              <div class="flex items-center gap-2">
                <img src="<?php echo e($order->book->cover_url); ?>" class="w-7 h-9 object-cover rounded" alt="">
                <span class="text-slate-700 text-xs truncate max-w-[120px]"><?php echo e($order->book->title); ?></span>
              </div>
            </td>
            <td class="py-2.5 text-xs text-slate-500"><?php echo e(Str::limit($order->user->name, 15)); ?></td>
            <td class="py-2.5 font-semibold text-xs text-slate-800"><?php echo e(number_format($order->amount,0,',',' ')); ?> XAF</td>
            <td class="py-2.5 font-semibold text-xs text-green-600">
              <?php echo e(number_format($order->amount * 0.80, 0, ',', ' ')); ?> XAF
            </td>
            <td class="py-2.5 text-xs text-slate-400"><?php echo e($order->created_at->format('d/m/Y')); ?></td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
new Chart(document.getElementById('revenueChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($monthlyRevenue->pluck('month'), 15, 512) ?>,
    datasets: [{
      label: 'Revenus',
      data: <?php echo json_encode($monthlyRevenue->pluck('total'), 15, 512) ?>,
      backgroundColor: 'rgba(37,99,235,0.8)',
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11 } } },
      y: { grid: { color: '#F8FAFC' }, ticks: { color: '#94A3B8', font: { size: 11 } } }
    }
  }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/dashboard.blade.php ENDPATH**/ ?>