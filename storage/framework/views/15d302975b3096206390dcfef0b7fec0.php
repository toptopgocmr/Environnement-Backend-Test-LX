<?php $__env->startSection('title','Mes Livres'); ?>
<?php $__env->startSection('page-title','Mes Livres'); ?>
<?php $__env->startSection('page-subtitle','Gérez votre catalogue'); ?>

<?php $__env->startSection('content'); ?>

<?php
  $activePlan = \App\Models\AuthorPlan::where('user_id', Auth::id())
      ->where('status','active')->with('plan')->latest()->first();
  $hasPlan = $activePlan && $activePlan->isActive();
?>

<?php if (! ($hasPlan)): ?>
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;">
  <i class="fa-solid fa-triangle-exclamation" style="color:#e67e00;font-size:22px;flex-shrink:0;"></i>
  <div style="flex:1;">
    <strong style="color:#7a4900;">Aucun forfait actif</strong>
    <p style="color:#7a4900;margin:2px 0 0;font-size:13px;">Vous ne pouvez pas publier de nouveau livre sans forfait. Souscrivez à un forfait pour accéder à la publication.</p>
  </div>
  <a href="<?php echo e(route('author.plans.index')); ?>"
     style="background:#ff9900;color:#fff;border-radius:7px;padding:8px 18px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
    Voir les forfaits
  </a>
</div>
<?php endif; ?>

<?php if($books->isEmpty()): ?>
<div class="flex flex-col items-center justify-center py-24 text-center">
  <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center mb-5">
    <i class="fa-solid fa-book-open text-blue-300 text-4xl"></i>
  </div>
  <h3 class="text-xl font-bold text-slate-700 mb-2">Aucun livre publié</h3>
  <p class="text-slate-400 mb-6">Commencez dès maintenant — publiez votre premier ouvrage sur LireX.</p>
  <?php if($hasPlan): ?>
  <a href="<?php echo e(route('author.books.create')); ?>" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-blue-700 transition">
    <i class="fa-solid fa-plus mr-2"></i>Publier un livre
  </a>
  <?php else: ?>
  <a href="<?php echo e(route('author.plans.index')); ?>" class="bg-amber-500 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-amber-600 transition">
    <i class="fa-solid fa-crown mr-2"></i>Choisir un forfait
  </a>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="grid grid-cols-3 gap-5">
  <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-md transition">
    <div class="relative">
      <img src="<?php echo e($book->cover_url); ?>" class="w-full h-44 object-cover" alt="">
      <div class="absolute top-3 left-3">
        <?php $bc=['published'=>'bg-green-500','pending'=>'bg-amber-500','rejected'=>'bg-red-500','draft'=>'bg-slate-500','suspended'=>'bg-orange-500']; ?>
        <span class="text-white text-xs font-semibold px-2.5 py-1 rounded-full <?php echo e($bc[$book->status] ?? 'bg-slate-500'); ?>"><?php echo e(ucfirst($book->status)); ?></span>
      </div>
      <?php if($book->is_featured): ?>
      <div class="absolute top-3 right-3 bg-amber-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">★</div>
      <?php endif; ?>
    </div>
    <div class="p-5">
      <h3 class="font-bold text-slate-800 mb-1 line-clamp-1"><?php echo e($book->title); ?></h3>
      <p class="text-blue-600 font-bold text-lg"><?php echo e($book->price_formatted); ?></p>
      <div class="flex items-center justify-between mt-3 text-xs text-slate-400">
        <span><i class="fa-solid fa-cart-shopping mr-1"></i><?php echo e($book->orders_count); ?> ventes</span>
        <span><i class="fa-solid fa-eye mr-1"></i><?php echo e(number_format($book->views)); ?> vues</span>
        <span><i class="fa-solid fa-star mr-1 text-amber-400"></i><?php echo e($book->average_rating); ?></span>
      </div>
      <div class="flex gap-2 mt-4">
        <a href="<?php echo e(route('author.books.stats',$book)); ?>" class="flex-1 text-center bg-blue-50 text-blue-600 py-2 rounded-xl text-xs font-semibold hover:bg-blue-100 transition">Stats</a>
        <?php if(in_array($book->status,['draft','rejected'])): ?>
        <a href="<?php echo e(route('author.books.edit',$book)); ?>" class="flex-1 text-center bg-slate-50 text-slate-600 py-2 rounded-xl text-xs font-semibold hover:bg-slate-100 transition">Modifier</a>
        <?php endif; ?>
        <?php if(in_array($book->status,['draft','rejected'])): ?>
        <form method="POST" action="<?php echo e(route('author.books.destroy',$book)); ?>" onsubmit="return confirm('Supprimer ?')">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button class="bg-red-50 text-red-500 py-2 px-3 rounded-xl text-xs hover:bg-red-100 transition"><i class="fa-solid fa-trash"></i></button>
        </form>
        <?php endif; ?>
      </div>
      <?php if($book->status==='rejected' && $book->rejection_reason): ?>
      <div class="mt-3 bg-red-50 border border-red-100 rounded-xl p-3 text-xs text-red-600">
        <strong>Rejeté :</strong> <?php echo e($book->rejection_reason); ?>

      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="mt-6"><?php echo e($books->links()); ?></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/books/index.blade.php ENDPATH**/ ?>