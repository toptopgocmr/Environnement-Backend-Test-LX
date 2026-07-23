<?php $__env->startSection('title',$book->title.' – Admin'); ?>
<?php $__env->startSection('page-title',$book->title); ?>
<?php $__env->startSection('page-subtitle','Détail & modération du livre'); ?>

<?php $__env->startSection('content'); ?>


<?php if(session('success')): ?>
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
  <i class="fa-solid fa-circle-check mr-2"></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-6">
  
  <div class="col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
      <div class="flex gap-6">
        
        <div class="flex-shrink-0 relative group cursor-pointer" onclick="document.getElementById('coverModal').classList.remove('hidden')">
          <?php if($book->cover_url): ?>
            <img src="<?php echo e($book->cover_url); ?>" class="w-32 h-44 object-cover rounded-xl shadow-lg" alt="">
          <?php else: ?>
            <div class="w-32 h-44 rounded-xl shadow-lg bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
              <i class="fa-solid fa-book text-white text-3xl opacity-50"></i>
            </div>
          <?php endif; ?>
          <div class="absolute inset-0 bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-1">
            <i class="fa-solid fa-camera text-white text-xl"></i>
            <span class="text-white text-xs font-semibold">Changer</span>
          </div>
        </div>

        <div class="flex-1">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-bold text-slate-800 mb-1"><?php echo e($book->title); ?></h2>
              <p class="text-slate-500 text-sm">par <span class="font-semibold text-blue-600"><?php echo e($book->author->name); ?></span></p>
            </div>
            <div class="flex items-center gap-2">
              <span class="badge-<?php echo e($book->status === 'published' ? 'published' : ($book->status === 'pending' ? 'pending' : 'rejected')); ?>"><?php echo e(ucfirst($book->status)); ?></span>
              <button onclick="document.getElementById('editInfoModal').classList.remove('hidden')"
                      class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1 rounded-lg font-semibold flex items-center gap-1">
                <i class="fa-solid fa-pen text-xs"></i> Éditer
              </button>
            </div>
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

      <button onclick="document.getElementById('coverModal').classList.remove('hidden')"
              class="w-full bg-indigo-50 text-indigo-600 border border-indigo-200 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition flex items-center justify-center gap-2 mb-3">
        <i class="fa-solid fa-image"></i> Changer la couverture
      </button>

      <button onclick="document.getElementById('editInfoModal').classList.remove('hidden')"
              class="w-full bg-slate-50 text-slate-600 border border-slate-200 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 transition flex items-center justify-center gap-2 mb-3">
        <i class="fa-solid fa-pen-to-square"></i> Modifier les infos
      </button>

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


<div id="coverModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-bold text-slate-800 text-lg">Changer la couverture</h3>
      <button onclick="document.getElementById('coverModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark text-xl"></i>
      </button>
    </div>
    <?php if($book->cover_url): ?>
    <div class="flex justify-center mb-4">
      <img src="<?php echo e($book->cover_url); ?>" class="h-36 object-cover rounded-xl shadow" alt="">
    </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('admin.books.update-cover',$book)); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div id="dropZone"
           class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition mb-4"
           onclick="document.getElementById('coverInput').click()"
           ondragover="event.preventDefault();this.classList.add('border-indigo-500','bg-indigo-50')"
           ondragleave="this.classList.remove('border-indigo-500','bg-indigo-50')"
           ondrop="handleDrop(event)">
        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
        <p class="text-sm font-semibold text-slate-600">Cliquer ou glisser une image ici</p>
        <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP · max 4 Mo</p>
        <input type="file" id="coverInput" name="cover" accept="image/*" class="hidden" onchange="previewCover(this)">
      </div>
      <img id="coverPreview" class="hidden mx-auto h-36 object-cover rounded-xl shadow mb-2" alt="">
      <p id="coverFileName" class="hidden text-xs text-center text-slate-500 mb-4"></p>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('coverModal').classList.add('hidden')"
                class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit"
                class="flex-1 bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700">
          <i class="fa-solid fa-floppy-disk mr-1"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>


<div id="editInfoModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-bold text-slate-800 text-lg">Modifier les informations</h3>
      <button onclick="document.getElementById('editInfoModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark text-xl"></i>
      </button>
    </div>
    <form method="POST" action="<?php echo e(route('admin.books.update-info',$book)); ?>">
      <?php echo csrf_field(); ?>
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="col-span-2">
          <label class="block text-xs font-semibold text-slate-600 mb-1">Titre</label>
          <input type="text" name="title" value="<?php echo e(old('title', $book->title)); ?>" required
                 class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Éditeur</label>
          <input type="text" name="publisher" value="<?php echo e(old('publisher', $book->publisher)); ?>"
                 class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Année de publication</label>
          <input type="number" name="publication_year" value="<?php echo e(old('publication_year', $book->publication_year)); ?>"
                 min="1800" max="2100"
                 class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Pages</label>
          <input type="number" name="pages" value="<?php echo e(old('pages', $book->pages)); ?>" min="1"
                 class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Prix (XAF)</label>
          <input type="number" name="price" value="<?php echo e(old('price', $book->price)); ?>" min="0"
                 class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-center gap-3 pt-4">
          <input type="checkbox" name="is_free" id="is_free" value="1" <?php echo e($book->is_free ? 'checked' : ''); ?>

                 class="w-4 h-4 rounded text-indigo-600">
          <label for="is_free" class="text-sm text-slate-700 font-medium">Livre gratuit</label>
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
          <textarea name="description" rows="5"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"><?php echo e(old('description', $book->description)); ?></textarea>
        </div>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('editInfoModal').classList.add('hidden')"
                class="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm text-slate-600 hover:bg-slate-50">Annuler</button>
        <button type="submit"
                class="flex-1 bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700">
          <i class="fa-solid fa-floppy-disk mr-1"></i> Enregistrer
        </button>
      </div>
    </form>
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
<script>
function openReject(id){ document.getElementById('rejectModal').classList.remove('hidden'); }

function previewCover(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('coverPreview');
      const name = document.getElementById('coverFileName');
      img.src = e.target.result;
      img.classList.remove('hidden');
      name.textContent = input.files[0].name;
      name.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').classList.remove('border-indigo-500','bg-indigo-50');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const input = document.getElementById('coverInput');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    previewCover(input);
  }
}

['coverModal','editInfoModal','rejectModal'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('click', e => { if (e.target === el) el.classList.add('hidden'); });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/books/show.blade.php ENDPATH**/ ?>