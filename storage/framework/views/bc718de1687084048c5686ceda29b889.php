<div class="space-y-4">
  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="text-xs text-gray-500 font-medium">Zone (code)</label>
      <input name="zone" required placeholder="congo / international"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 font-medium">Libellé affiché</label>
      <input name="label" required placeholder="Congo-Brazzaville"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="text-xs text-gray-500 font-medium">Frais de base (XAF)</label>
      <input name="base_price" type="number" min="0" required placeholder="1500"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 font-medium">+/kg suppl. (XAF)</label>
      <input name="price_per_kg" type="number" min="0" placeholder="0"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
  </div>

  <div>
    <label class="text-xs text-gray-500 font-medium">Gratuit dès (XAF, 0 = jamais)</label>
    <input name="free_above" type="number" min="0" placeholder="25000"
           class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="text-xs text-gray-500 font-medium">Délai min (jours)</label>
      <input name="estimated_days_min" type="number" min="1" required placeholder="1"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 font-medium">Délai max (jours)</label>
      <input name="estimated_days_max" type="number" min="1" required placeholder="3"
             class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
  </div>

  <div>
    <label class="text-xs text-gray-500 font-medium">Notes (optionnel)</label>
    <textarea name="notes" rows="2" placeholder="Informations sur cette zone..."
              class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
  </div>

  <label class="flex items-center gap-2 cursor-pointer">
    <input name="is_active" type="checkbox" checked class="rounded">
    <span class="text-sm text-gray-600">Zone active</span>
  </label>
</div>
<?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/shipping_rates/_form.blade.php ENDPATH**/ ?>