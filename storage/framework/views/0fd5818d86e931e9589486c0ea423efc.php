<?php $__env->startSection('title', 'Frais de livraison – LireX Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">

  
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">🚚 Frais de livraison</h1>
      <p class="text-gray-500 text-sm mt-1">Gérez les tarifs de livraison pour le Congo et l'international.</p>
    </div>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
      <i class="fas fa-plus"></i> Nouvelle zone
    </button>
  </div>

  
  <?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
      ✅ <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php $__currentLoopData = $rates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 flex items-center justify-between
                  <?php echo e($rate->zone === 'congo' ? 'bg-green-50 border-b border-green-100' : 'bg-blue-50 border-b border-blue-100'); ?>">
        <div class="flex items-center gap-3">
          <span class="text-2xl"><?php echo e($rate->zone === 'congo' ? '🇨🇬' : '🌍'); ?></span>
          <div>
            <p class="font-bold text-gray-800"><?php echo e($rate->label); ?></p>
            <p class="text-xs text-gray-500">Zone : <?php echo e($rate->zone); ?></p>
          </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold
                     <?php echo e($rate->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'); ?>">
          <?php echo e($rate->is_active ? 'Actif' : 'Inactif'); ?>

        </span>
      </div>

      <div class="px-6 py-5 grid grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-gray-400 text-xs uppercase tracking-wide">Frais de base</p>
          <p class="font-bold text-gray-800 text-lg"><?php echo e(number_format($rate->base_price, 0, ',', ' ')); ?> XAF</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs uppercase tracking-wide">+/kg suppl.</p>
          <p class="font-bold text-gray-800 text-lg"><?php echo e(number_format($rate->price_per_kg, 0, ',', ' ')); ?> XAF</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs uppercase tracking-wide">Gratuit dès</p>
          <p class="font-semibold <?php echo e($rate->free_above > 0 ? 'text-green-600' : 'text-gray-400'); ?>">
            <?php echo e($rate->free_above > 0 ? number_format($rate->free_above, 0, ',', ' ') . ' XAF' : 'Jamais'); ?>

          </p>
        </div>
        <div>
          <p class="text-gray-400 text-xs uppercase tracking-wide">Délai estimé</p>
          <p class="font-semibold text-gray-800"><?php echo e($rate->delivery_range); ?></p>
        </div>
        <?php if($rate->notes): ?>
        <div class="col-span-2">
          <p class="text-gray-400 text-xs uppercase tracking-wide">Notes</p>
          <p class="text-gray-600 text-sm"><?php echo e($rate->notes); ?></p>
        </div>
        <?php endif; ?>
      </div>

      <div class="px-6 pb-4 flex gap-2">
        <button onclick='openEditModal(<?php echo e(json_encode($rate)); ?>)'
                class="flex-1 border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium">
          <i class="fas fa-edit mr-1"></i> Modifier
        </button>
        <form method="POST" action="<?php echo e(route('admin.shipping-rates.destroy', $rate)); ?>"
              onsubmit="return confirm('Supprimer cette zone ?')">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button type="submit" class="border border-red-200 text-red-500 hover:bg-red-50 px-3 py-2 rounded-lg text-sm">
            <i class="fas fa-trash"></i>
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="font-bold text-gray-800 mb-4">🧮 Simulateur de frais</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm text-gray-500">Zone</label>
        <select id="sim-zone" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          <?php $__currentLoopData = $rates->where('is_active', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($rate->id); ?>" data-base="<?php echo e($rate->base_price); ?>" data-pkg="<?php echo e($rate->price_per_kg); ?>" data-free="<?php echo e($rate->free_above); ?>">
              <?php echo e($rate->label); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div>
        <label class="text-sm text-gray-500">Montant commande (XAF)</label>
        <input id="sim-amount" type="number" value="10000" min="0"
               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" oninput="simulate()">
      </div>
      <div>
        <label class="text-sm text-gray-500">Poids (grammes)</label>
        <input id="sim-weight" type="number" value="500" min="0"
               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" oninput="simulate()">
      </div>
    </div>
    <div class="mt-4 p-4 bg-amber-50 rounded-xl text-center">
      <p class="text-sm text-gray-500">Frais de livraison estimés</p>
      <p id="sim-result" class="text-2xl font-bold text-amber-600 mt-1">— XAF</p>
    </div>
  </div>
</div>


<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h3 class="font-bold text-lg mb-4">Nouvelle zone de livraison</h3>
    <form method="POST" action="<?php echo e(route('admin.shipping-rates.store')); ?>">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('admin.shipping_rates._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="flex gap-3 mt-6">
        <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                class="flex-1 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm">Annuler</button>
        <button type="submit" class="flex-1 bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          Ajouter
        </button>
      </div>
    </form>
  </div>
</div>


<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h3 class="font-bold text-lg mb-4">Modifier la zone</h3>
    <form id="edit-form" method="POST">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('admin.shipping_rates._form', ['edit' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="flex gap-3 mt-6">
        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                class="flex-1 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm">Annuler</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(rate) {
  const form = document.getElementById('edit-form');
  form.action = '/admin/shipping-rates/' + rate.id;
  form.querySelector('[name=zone]').value = rate.zone;
  form.querySelector('[name=label]').value = rate.label;
  form.querySelector('[name=base_price]').value = rate.base_price;
  form.querySelector('[name=price_per_kg]').value = rate.price_per_kg;
  form.querySelector('[name=free_above]').value = rate.free_above;
  form.querySelector('[name=estimated_days_min]').value = rate.estimated_days_min;
  form.querySelector('[name=estimated_days_max]').value = rate.estimated_days_max;
  form.querySelector('[name=is_active]').checked = rate.is_active;
  form.querySelector('[name=notes]').value = rate.notes ?? '';
  document.getElementById('modal-edit').classList.remove('hidden');
}

function simulate() {
  const sel    = document.getElementById('sim-zone');
  const opt    = sel.options[sel.selectedIndex];
  const base   = parseInt(opt.dataset.base) || 0;
  const pkg    = parseInt(opt.dataset.pkg)  || 0;
  const free   = parseInt(opt.dataset.free) || 0;
  const amount = parseInt(document.getElementById('sim-amount').value) || 0;
  const weight = parseInt(document.getElementById('sim-weight').value) || 0;

  let fee = base;
  if (free > 0 && amount >= free) fee = 0;
  else if (weight > 1000 && pkg > 0) fee += Math.ceil((weight - 1000) / 1000) * pkg;

  document.getElementById('sim-result').textContent =
    fee === 0 ? 'Gratuit 🎉' : fee.toLocaleString('fr-FR') + ' XAF';
}

document.getElementById('sim-zone').addEventListener('change', simulate);
simulate();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/shipping_rates/index.blade.php ENDPATH**/ ?>