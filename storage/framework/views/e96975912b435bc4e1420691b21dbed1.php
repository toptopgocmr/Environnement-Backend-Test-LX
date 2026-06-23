<?php $__env->startSection('title',$book->title.' – Admin'); ?>
<?php $__env->startSection('page-title',$book->title); ?>
<?php $__env->startSection('page-subtitle','Détail & modération du livre'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-3 gap-6">
  
  <div class="col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
      <div class="flex gap-6">
        <img src="<?php echo e($book->cover_url); ?>" class="w-32 h-44 object-cover rounded-xl shadow-lg flex-shrink-0" alt="">
        <div class="flex-1">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-bold text-slate-800 mb-1"><?php echo e($book->title); ?></h2>
              <p class="text-slate-500 text-sm">par <span class="font-semibold text-blue-600"><?php echo e($book->author->name); ?></span></p>
            </div>
            <span class="badge-<?php echo e($book->status === 'published' ? 'published' : ($book->status === 'pending' ? 'pending' : 'rejected')); ?>"><?php echo e(ucfirst($book->status)); ?></span>
          </div>
          <div class="grid grid-cols-3 gap-4 mt-4 text-sm">
            <div><p class="text-slate-400 text-xs">Prix</p><p class="font-bold text-slate-800"><?php echo e($book->price_formatted); ?></p></div>
            <div><p class="text-slate-400 text-xs">Format</p><p class="font-semibold text-slate-700"><?php echo e(strtoupper($book->format)); ?></p></div>
            <div><p class="text-slate-400 text-xs">Langue</p><p class="font-semibold text-slate-700"><?php echo e(strtoupper($book->language)); ?></p></div>
            <div><p class="text-slate-400 text-xs">Pages</p><p class="font-semibold text-slate-700"><?php echo e($book->pages ?? '—'); ?></p></div>
            <div><p class="text-slate-400 text-xs">Vues</p><p class="font-semibold text-slate-700"><?php echo e(number_format($book->views)); ?></p></div>
            <div><p class="text-slate-400 text-xs">Téléchargements</p><p class="font-semibold text-slate-700"><?php echo e(number_format($book->downloads)); ?></p></div>
          </div>
          <?php if($book->tags->count()): ?>
          <div class="flex flex-wrap gap-2 mt-4">
            <?php $__currentLoopData = $book->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full"><?php echo e($tag->tag); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="mt-5 pt-5 border-t border-slate-100">
        <p class="text-sm font-semibold text-slate-600 mb-2">Description</p>
        <p class="text-slate-500 text-sm leading-relaxed"><?php echo e($book->description); ?></p>
      </div>
    </div>

    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
      <h3 class="font-bold text-slate-800 mb-4">Statistiques de ventes</h3>
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-blue-700"><?php echo e($orderStats['total']); ?></p>
          <p class="text-xs text-blue-500 mt-1">Ventes totales</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-green-700"><?php echo e(number_format($orderStats['revenue'],0,',',' ')); ?> XAF</p>
          <p class="text-xs text-green-500 mt-1">Revenus générés</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-amber-700"><?php echo e($book->average_rating); ?>/5</p>
          <p class="text-xs text-amber-500 mt-1">Note moyenne (<?php echo e($book->ratings_count); ?> avis)</p>
        </div>
      </div>
    </div>

    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
      <h3 class="font-bold text-slate-800 mb-4">Avis lecteurs (<?php echo e($book->reviews->count()); ?>)</h3>
      <?php $__empty_1 = true; $__currentLoopData = $book->reviews->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="flex gap-3 py-3 border-b border-slate-50 last:border-0">
        <img src="<?php echo e($review->user->avatar_url); ?>" class="w-8 h-8 rounded-full object-cover" alt="">
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700"><?php echo e($review->user->name); ?></p>
            <div class="flex gap-0.5">
              <?php for($i=1;$i<=5;$i++): ?><span class="<?php echo e($i<=$review->rating?'text-amber-400':'text-slate-200'); ?> text-sm">★</span><?php endfor; ?>
            </div>
          </div>
          <p class="text-xs text-slate-400 mt-0.5"><?php echo e($review->created_at->format('d/m/Y')); ?></p>
          <?php if($review->comment): ?><p class="text-sm text-slate-500 mt-1"><?php echo e($review->comment); ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <p class="text-slate-400 text-sm">Aucun avis pour ce livre.</p>
      <?php endif; ?>
    </div>
  </div>

  
  <div class="space-y-4">
    
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <h3 class="font-bold text-slate-800 mb-4">Actions</h3>

      <?php if($book->status === 'pending'): ?>
      <form method="POST" action="<?php echo e(route('admin.books.approve',$book)); ?>" class="mb-3">
        <?php echo csrf_field(); ?>
        <button class="w-full bg-green-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-check"></i> Approuver & Publier
        </button>
      </form>
      <button onclick="openReject(<?php echo e($book->id); ?>)" class="w-full bg-red-50 text-red-600 border border-red-200 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-100 transition flex items-center justify-center gap-2 mb-3">
        <i class="fa-solid fa-xmark"></i> Rejeter
      </button>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('admin.books.featured',$book)); ?>" class="mb-3">
        <?php echo csrf_field(); ?>
        <button class="w-full <?php echo e($book->is_featured?'bg-amber-500 text-white':'bg-amber-50 text-amber-600 border border-amber-200'); ?> py-2.5 rounded-xl text-sm font-semibold hover:opacity-80 transition flex items-center justify-center gap-2">
          <i class="fa-<?php echo e($book->is_featured?'solid':'regular'); ?> fa-star"></i>
          <?php echo e($book->is_featured ? 'Retirer de la sélection' : 'Mettre en avant'); ?>

        </button>
      </form>

      <form method="POST" action="<?php echo e(route('admin.books.destroy',$book)); ?>" onsubmit="return confirm('Supprimer définitivement ce livre ?')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button class="w-full bg-slate-50 text-slate-500 border border-slate-200 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-trash"></i> Supprimer
        </button>
      </form>
    </div>

    
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
      <h3 class="font-bold text-slate-800 mb-4">Auteur</h3>
      <div class="flex items-center gap-3 mb-4">
        <img src="<?php echo e($book->author->avatar_url); ?>" class="w-12 h-12 rounded-full object-cover" alt="">
        <div>
          <p class="font-semibold text-slate-800"><?php echo e($book->author->name); ?></p>
          <p class="text-xs text-slate-400"><?php echo e($book->author->email); ?></p>
          <?php if($book->author->is_verified_author): ?>
          <span class="text-xs text-blue-600 flex items-center gap-1 mt-0.5"><i class="fa-solid fa-circle-check text-xs"></i> Vérifié</span>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?php echo e(route('admin.users.show',$book->author)); ?>" class="text-blue-600 text-sm font-semibold hover:underline">Voir profil auteur →</a>
    </div>

    
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-sm space-y-3">
      <div class="flex justify-between"><span class="text-slate-400">Soumis le</span><span class="font-semibold text-slate-700"><?php echo e($book->created_at->format('d/m/Y')); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">Catégorie</span><span class="font-semibold text-slate-700"><?php echo e($book->category?->name ?? '—'); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">ISBN</span><span class="font-semibold text-slate-700"><?php echo e($book->isbn ?? '—'); ?></span></div>
      <div class="flex justify-between"><span class="text-slate-400">Éditeur</span><span class="font-semibold text-slate-700"><?php echo e($book->publisher ?? '—'); ?></span></div>
      <?php if($book->rejection_reason): ?>
      <div class="pt-3 border-t border-slate-100">
        <p class="text-xs font-semibold text-red-600 mb-1">Raison du rejet :</p>
        <p class="text-xs text-slate-500"><?php echo e($book->rejection_reason); ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>


<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-4">Rejeter « <?php echo e($book->title); ?> »</h3>
    <form method="POST" action="<?php echo e(route('admin.books.reject',$book)); ?>">
      <?php echo csrf_field(); ?>
      <textarea name="reason" rows="4" required placeholder="Raison du rejet…" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-red-700">Rejeter</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>function openReject(id){ document.getElementById('rejectModal').classList.remove('hidden'); }</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/books/show.blade.php ENDPATH**/ ?>