<?php $__env->startSection('title','Livres – LireX Admin'); ?>
<?php $__env->startSection('page-title','Gestion des Livres'); ?>
<?php $__env->startSection('page-subtitle','Modération et suivi de tous les ouvrages'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white rounded-2xl p-5 mb-6 shadow-sm border border-slate-100">
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[180px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Recherche</label>
      <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Titre, auteur…"
        class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>
    <div class="min-w-[140px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Statut</label>
      <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Tous</option>
        <?php $__currentLoopData = ['pending'=>'En attente','published'=>'Publié','rejected'=>'Rejeté','draft'=>'Brouillon','suspended'=>'Suspendu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>
    <div class="min-w-[160px]">
      <label class="text-xs font-semibold text-slate-500 mb-1 block">Catégorie</label>
      <select name="category_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Toutes</option>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($cat->id); ?>" <?php if(request('category_id')==$cat->id): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    <a href="<?php echo e(route('admin.books.index')); ?>" class="text-slate-400 text-sm hover:text-slate-600 py-2">Réinitialiser</a>
  </form>
</div>


<?php
  $statuses = ['pending'=>['En attente','amber'], 'published'=>['Publiés','green'], 'rejected'=>['Rejetés','red'], 'draft'=>['Brouillons','slate']];
?>
<div class="grid grid-cols-4 gap-4 mb-6">
  <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st=>[$label,$color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center gap-3">
    <div class="w-9 h-9 bg-<?php echo e($color); ?>-100 rounded-lg flex items-center justify-center">
      <div class="w-2.5 h-2.5 rounded-full bg-<?php echo e($color); ?>-500"></div>
    </div>
    <div>
      <p class="font-bold text-slate-800"><?php echo e(\App\Models\Book::where('status',$st)->count()); ?></p>
      <p class="text-xs text-slate-400"><?php echo e($label); ?></p>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <?php $__currentLoopData = ['Livre','Auteur','Catégorie','Prix','Statut','Ventes','Actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider"><?php echo e($h); ?></th>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <img src="<?php echo e($book->cover_url); ?>" class="w-10 h-14 object-cover rounded-lg shadow-sm" alt="">
              <div>
                <p class="font-semibold text-slate-800 max-w-[180px] truncate"><?php echo e($book->title); ?></p>
                <p class="text-xs text-slate-400"><?php echo e(strtoupper($book->language)); ?> · <?php echo e(strtoupper($book->format)); ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <img src="<?php echo e($book->author->avatar_url); ?>" class="w-7 h-7 rounded-full object-cover" alt="">
              <span class="text-slate-600"><?php echo e($book->author->name); ?></span>
            </div>
          </td>
          <td class="px-5 py-3 text-slate-500"><?php echo e($book->category?->name ?? '—'); ?></td>
          <td class="px-5 py-3 font-semibold text-slate-800"><?php echo e($book->price_formatted); ?></td>
          <td class="px-5 py-3">
            <?php $badges=['pending'=>'badge-pending','published'=>'badge-published','rejected'=>'badge-rejected','draft'=>'badge-draft','suspended'=>'bg-orange-100 text-orange-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']; ?>
            <span class="<?php echo e($badges[$book->status] ?? 'badge-draft'); ?>"><?php echo e(ucfirst($book->status)); ?></span>
          </td>
          <td class="px-5 py-3 text-slate-600"><?php echo e($book->orders()->where('payment_status','paid')->count()); ?></td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <a href="<?php echo e(route('admin.books.show',$book)); ?>" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Voir"><i class="fa-solid fa-eye text-sm"></i></a>
              <?php if($book->status==='pending'): ?>
              <form method="POST" action="<?php echo e(route('admin.books.approve',$book)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Approuver"><i class="fa-solid fa-check text-sm"></i></button>
              </form>
              <?php endif; ?>
              <form method="POST" action="<?php echo e(route('admin.books.featured',$book)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button class="p-1.5 <?php echo e($book->is_featured?'text-amber-500':'text-slate-400'); ?> hover:bg-amber-50 rounded-lg transition" title="<?php echo e($book->is_featured?'Retirer':'Mettre en avant'); ?>">
                  <i class="fa-<?php echo e($book->is_featured?'solid':'regular'); ?> fa-star text-sm"></i>
                </button>
              </form>
              <form method="POST" action="<?php echo e(route('admin.books.destroy',$book)); ?>" class="inline" onsubmit="return confirm('Supprimer ce livre ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Supprimer"><i class="fa-solid fa-trash text-sm"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="px-5 py-16 text-center text-slate-400"><i class="fa-solid fa-book-open text-3xl mb-3 block opacity-30"></i>Aucun livre trouvé</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="px-5 py-4 border-t border-slate-100"><?php echo e($books->withQueryString()->links()); ?></div>
</div>


<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <h3 class="font-bold text-slate-800 text-lg mb-4">Rejeter le livre</h3>
    <form method="POST" id="rejectForm">
      <?php echo csrf_field(); ?>
      <label class="text-sm font-semibold text-slate-600 mb-2 block">Raison du rejet</label>
      <textarea name="reason" rows="4" required placeholder="Expliquez pourquoi ce livre est rejeté…" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
      <div class="flex gap-3 mt-4">
        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="flex-1 border border-slate-200 rounded-xl py-2 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2 text-sm font-semibold hover:bg-red-700">Rejeter</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openReject(bookId) {
  document.getElementById('rejectForm').action = `/admin/books/${bookId}/reject`;
  document.getElementById('rejectModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/books/index.blade.php ENDPATH**/ ?>