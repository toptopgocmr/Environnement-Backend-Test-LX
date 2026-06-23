<?php $__env->startSection('title', 'Mes Forfaits – LireX'); ?>
<?php $__env->startSection('page-title', 'Forfaits de publication'); ?>
<?php $__env->startSection('page-subtitle', 'Choisissez le forfait adapté à vos ambitions'); ?>

<?php $__env->startSection('content'); ?>


<?php if($activePlan): ?>
<div class="mb-8 p-5 rounded-2xl border flex items-center gap-5"
     style="background:linear-gradient(135deg,#0f2044,#1e3a6a);border-color:#2563eb;">
  <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
    <i class="fa-solid fa-crown text-white text-lg"></i>
  </div>
  <div class="flex-1">
    <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Forfait actif</p>
    <p class="text-white font-bold text-xl"><?php echo e($activePlan->plan->name); ?></p>
    <p class="text-slate-400 text-sm mt-0.5">
      Facturation <?php echo e($activePlan->billing === 'annual' ? 'annuelle' : 'mensuelle'); ?>

      · Expire le <?php echo e($activePlan->ends_at?->format('d/m/Y') ?? '–'); ?>

      · <?php echo e(number_format($activePlan->plan->royalty_percent, 0)); ?>% de royalties
    </p>
  </div>
  <span class="px-3 py-1 rounded-full text-xs font-bold
    <?php echo e($activePlan->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400'); ?>">
    <?php echo e($activePlan->status === 'active' ? '✓ Actif' : '⏳ En attente de validation'); ?>

  </span>
</div>
<?php else: ?>
<div class="mb-8 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-3">
  <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
  Vous n'avez pas encore de forfait actif. Choisissez un forfait pour publier vos livres.
</div>
<?php endif; ?>


<div class="flex items-center justify-center gap-3 mb-8">
  <span class="text-slate-600 text-sm font-medium">Mensuel</span>
  <button onclick="toggleBilling()"
    class="relative w-12 h-6 rounded-full transition-colors duration-300 focus:outline-none"
    id="billing-toggle" style="background:#e2e8f0;">
    <span id="toggle-dot"
      class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-300"></span>
  </button>
  <span class="text-slate-600 text-sm font-medium">Annuel
    <span class="ml-1 text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">–20%</span>
  </span>
</div>


<?php if($plans->count() > 0): ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo e(min($plans->count(), 4)); ?> gap-5">
  <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $isActive   = $activePlan && $activePlan->plan_id === $plan->id && $activePlan->status === 'active';
    $isFeatured = $loop->index === 1;
  ?>
  <div class="relative rounded-2xl border flex flex-col transition-all hover:shadow-lg
    <?php echo e($isFeatured ? 'border-blue-500 shadow-blue-100 shadow-lg ring-2 ring-blue-500/30' : 'border-slate-200'); ?>"
    style="background:#fff;">

    <?php if($isFeatured): ?>
    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
      <span class="bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full shadow">⭐ Populaire</span>
    </div>
    <?php endif; ?>

    <div class="p-6 border-b border-slate-100">
      <h3 class="text-lg font-bold text-slate-800"><?php echo e($plan->name); ?></h3>
      <p class="text-slate-500 text-sm mt-1"><?php echo e($plan->description); ?></p>
      <div class="mt-4">
        <div id="price-monthly-<?php echo e($plan->id); ?>">
          <?php if($plan->price_monthly == 0): ?>
            <span class="text-3xl font-black text-slate-800">Gratuit</span>
          <?php else: ?>
            <span class="text-3xl font-black text-slate-800"><?php echo e(number_format($plan->price_monthly, 0, ',', ' ')); ?></span>
            <span class="text-slate-500 text-sm"> <?php echo e($plan->currency); ?>/mois</span>
          <?php endif; ?>
        </div>
        <div id="price-annual-<?php echo e($plan->id); ?>" class="hidden">
          <?php if($plan->price_annual == 0): ?>
            <span class="text-3xl font-black text-slate-800">Gratuit</span>
          <?php else: ?>
            <span class="text-3xl font-black text-blue-600"><?php echo e(number_format($plan->price_annual, 0, ',', ' ')); ?></span>
            <span class="text-slate-500 text-sm"> <?php echo e($plan->currency); ?>/an</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="p-6 flex-1 space-y-3">
      <div class="flex items-center gap-2 text-sm text-slate-600">
        <i class="fa-solid fa-book text-blue-500 w-4"></i>
        <span><?php echo e($plan->max_books === -1 ? 'Livres illimités' : $plan->max_books . ' livre(s) max'); ?></span>
      </div>
      <div class="flex items-center gap-2 text-sm text-slate-600">
        <i class="fa-solid fa-file-arrow-up text-blue-500 w-4"></i>
        <span>Fichiers jusqu'à <?php echo e($plan->max_file_size_mb); ?> Mo</span>
      </div>
      <div class="flex items-center gap-2 text-sm <?php echo e($plan->royalty_percent >= 80 ? 'text-green-700 font-semibold' : 'text-slate-600'); ?>">
        <i class="fa-solid fa-percent text-green-500 w-4"></i>
        <span><?php echo e(number_format($plan->royalty_percent, 0)); ?>% de royalties</span>
      </div>
      <div class="flex items-center gap-2 text-sm <?php echo e($plan->allow_physical ? 'text-slate-700' : 'text-slate-400'); ?>">
        <i class="fa-solid fa-truck w-4 <?php echo e($plan->allow_physical ? 'text-blue-500' : 'text-slate-300'); ?>"></i>
        <span class="<?php echo e(!$plan->allow_physical ? 'line-through' : ''); ?>">Vente physique</span>
      </div>
      <div class="flex items-center gap-2 text-sm <?php echo e($plan->allow_academic ? 'text-slate-700' : 'text-slate-400'); ?>">
        <i class="fa-solid fa-graduation-cap w-4 <?php echo e($plan->allow_academic ? 'text-blue-500' : 'text-slate-300'); ?>"></i>
        <span class="<?php echo e(!$plan->allow_academic ? 'line-through' : ''); ?>">Thèses & mémoires</span>
      </div>
      <div class="flex items-center gap-2 text-sm <?php echo e($plan->ai_review ? 'text-slate-700' : 'text-slate-400'); ?>">
        <i class="fa-solid fa-robot w-4 <?php echo e($plan->ai_review ? 'text-purple-500' : 'text-slate-300'); ?>"></i>
        <span>Analyse IA</span>
      </div>
      <?php if($plan->features && count($plan->features)): ?>
      <div class="pt-2 border-t border-slate-100 space-y-1.5">
        <?php $__currentLoopData = $plan->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <i class="fa-solid fa-check text-green-500 w-4 text-xs"></i>
          <span><?php echo e($f); ?></span>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="p-6 pt-0">
      <?php if($isActive): ?>
      <div class="w-full py-2.5 rounded-xl text-center text-sm font-semibold bg-green-50 text-green-700 border border-green-200">
        ✓ Forfait actuel
      </div>
      <?php else: ?>
      <button type="button"
        onclick="openPayModal(<?php echo e($plan->id); ?>,'<?php echo e(addslashes($plan->name)); ?>',<?php echo e($plan->price_monthly); ?>,<?php echo e($plan->price_annual); ?>,'<?php echo e($plan->currency); ?>')"
        class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all
          <?php echo e($isFeatured ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-md' : 'bg-slate-800 text-white hover:bg-slate-900'); ?>">
        <?php echo e($plan->price_monthly == 0 ? 'Choisir ce forfait' : 'Souscrire'); ?> →
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>




<div id="payModal" class="fixed inset-0 z-50 hidden items-center justify-center"
     style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px);">
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col" style="max-height:90vh;">

    
    <div style="background:#232f3e;" class="px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-crown text-amber-400"></i>
        <span class="text-white font-bold" id="modal-plan-name">—</span>
      </div>
      <button onclick="closePayModal()" class="text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    
    <div class="flex border-b border-slate-100" id="step-tabs">
      <div class="flex-1 py-3 text-center text-xs font-semibold" id="tab-1" style="color:#ff9900;border-bottom:2px solid #ff9900;">
        <span id="tab-num-1" class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs mr-1" style="background:#fff3cd;color:#d97706;">1</span>Récapitulatif
      </div>
      <div class="flex-1 py-3 text-center text-xs font-semibold" id="tab-2" style="color:#94a3b8;">
        <span id="tab-num-2" class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs mr-1" style="background:#f1f5f9;color:#94a3b8;">2</span>Paiement
      </div>
      <div class="flex-1 py-3 text-center text-xs font-semibold" id="tab-3" style="color:#94a3b8;">
        <span id="tab-num-3" class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs mr-1" style="background:#f1f5f9;color:#94a3b8;">3</span>Confirmation
      </div>
    </div>

    <form id="pay-form" method="POST" action="<?php echo e(route('author.plans.subscribe')); ?>" class="overflow-y-auto flex-1">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="plan_id"        id="f-plan-id">
      <input type="hidden" name="billing"         id="f-billing" value="monthly">
      <input type="hidden" name="payment_method"  id="f-method">
      <input type="hidden" name="transaction_ref" id="f-txref">
      <input type="hidden" name="phone_used"      id="f-phone">

      
      <div id="step-1" class="p-4">
        <h3 class="font-bold text-slate-800 mb-3">Votre commande</h3>
        <div class="rounded-xl border border-slate-200 overflow-hidden mb-3">
          <div class="flex items-center justify-between px-3 py-2 bg-slate-50">
            <span class="text-sm text-slate-600">Forfait</span>
            <span class="font-semibold text-slate-800" id="recap-plan">—</span>
          </div>
          <div class="flex items-center justify-between px-3 py-2 border-t border-slate-100">
            <span class="text-sm text-slate-600">Facturation</span>
            <span class="font-semibold text-slate-800" id="recap-billing">Mensuelle</span>
          </div>
          <div class="flex items-center justify-between px-3 py-2 border-t border-slate-100 bg-amber-50">
            <span class="text-sm font-semibold text-slate-700">Total à payer</span>
            <span class="font-black text-base text-amber-600" id="recap-price">—</span>
          </div>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Période de facturation</label>
          <div class="grid grid-cols-2 gap-2">
            <label id="lbl-monthly" class="flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer" style="border-color:#f59e0b;background:#fffbeb;">
              <input type="radio" name="billing_ui" value="monthly" checked class="accent-amber-500">
              <div>
                <div class="font-semibold text-slate-800 text-sm">Mensuel</div>
                <div class="text-slate-500 text-xs" id="opt-monthly-price">—</div>
              </div>
            </label>
            <label id="lbl-annual" class="flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer" style="border-color:#e2e8f0;background:white;">
              <input type="radio" name="billing_ui" value="annual" class="accent-amber-500">
              <div>
                <div class="font-semibold text-slate-800 text-sm">Annuel <span class="text-green-600 text-xs">–20%</span></div>
                <div class="text-slate-500 text-xs" id="opt-annual-price">—</div>
              </div>
            </label>
          </div>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Méthode de paiement</label>
          <div class="space-y-1.5">
            <label class="pay-meth flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer" data-m="mtn_momo" style="border-color:#f59e0b;background:#fffbeb;">
              <input type="radio" name="pay_method_ui" value="mtn_momo" checked class="accent-amber-500">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#ffcc00;flex-shrink:0;">
                <svg width="20" height="12" viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M0 0h7l5 14L17 0h7v24h-5V8l-4 11h-5L6 8v16H0V0z" fill="#000"/>
                  <path d="M25 0h15v4h-5v20h-5V4h-5V0z" fill="#000"/>
                </svg>
              </span>
              <div><div class="font-semibold text-sm text-slate-800">MTN Mobile Money</div><div class="text-xs text-slate-500">Via USSD *105#</div></div>
            </label>
            <label class="pay-meth flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer" data-m="airtel_money" style="border-color:#e2e8f0;background:white;">
              <input type="radio" name="pay_method_ui" value="airtel_money" class="accent-red-500">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e40000;flex-shrink:0;">
                <svg width="22" height="16" viewBox="0 0 44 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M22 0C10 0 1 7 0 17c1-7 8-12 16-12 10 0 16 7 15 16 2-2 3-5 3-7 0-8-5-14-12-14z" fill="#fff"/>
                  <path d="M22 28C34 28 43 21 44 11c-1 7-8 12-16 12C18 23 12 16 13 7c-2 2-3 5-3 7 0 8 5 14 12 14z" fill="#fff"/>
                </svg>
              </span>
              <div><div class="font-semibold text-sm text-slate-800">Airtel Money</div><div class="text-xs text-slate-500">Via USSD *128#</div></div>
            </label>
            <label class="pay-meth flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer" data-m="stripe" style="border-color:#e2e8f0;background:white;">
              <input type="radio" name="pay_method_ui" value="stripe" class="accent-blue-500">
              <span class="text-lg">💳</span>
              <div><div class="font-semibold text-sm text-slate-800">Carte bancaire (Stripe)</div><div class="text-xs text-slate-500">Visa, Mastercard</div></div>
            </label>
          </div>
        </div>

        <button type="button" onclick="goStep(2)" class="w-full py-2.5 rounded-xl font-semibold text-white" style="background:#ff9900;">
          Continuer → Instructions de paiement
        </button>
      </div>

      
      <div id="step-2" class="p-4 hidden">

        
        <div id="pay-momo">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl" id="momo-icon">🟡</span>
            <div>
              <h3 class="font-bold text-slate-800" id="momo-title">MTN Mobile Money</h3>
              <p class="text-slate-500 text-sm">Entrez votre numéro — vous recevrez une notification sur votre téléphone</p>
            </div>
          </div>

          
          <div class="rounded-xl p-3 mb-5 flex items-center justify-between" style="background:#f8fafc;border:1.5px solid #e2e8f0;">
            <span class="text-sm text-slate-600">Montant à payer</span>
            <span class="font-black text-lg text-amber-600" id="momo-amount">—</span>
          </div>

          
          <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Votre numéro Mobile Money <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-2">
              <div class="flex items-center px-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-600 text-sm font-medium select-none" style="min-width:64px;">+242</div>
              <input type="tel" id="inp-momo-phone"
                placeholder="06 123 45 67"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                maxlength="15">
            </div>
            <p class="text-xs text-slate-400 mt-1.5">
              <span id="momo-phone-hint">Le numéro MTN MoMo enregistré sur votre téléphone.</span>
            </p>
          </div>

          <div id="momo-error" class="hidden mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm"></div>

          <div class="flex gap-3 mt-2">
            <button type="button" onclick="goStep(1)" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">← Retour</button>
            <button type="button" id="btn-pay-push" onclick="triggerPushPayment()"
              class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
              style="background:#ff9900;">
              <i class="fa-solid fa-mobile-screen mr-1"></i> Envoyer la demande
            </button>
          </div>
        </div>

        
        <div id="pay-stripe" class="hidden">
          <div class="flex items-center gap-3 mb-4"><span class="text-3xl">💳</span>
            <div><h3 class="font-bold text-slate-800">Carte bancaire</h3><p class="text-slate-500 text-sm">Paiement sécurisé via Stripe</p></div>
          </div>
          <div class="rounded-xl p-5 bg-blue-50 border border-blue-200 text-blue-800 text-sm mb-5">
            <i class="fa-solid fa-lock mr-2"></i> Vous serez redirigé vers la page Stripe sécurisée. Votre forfait sera activé instantanément.
          </div>
          <div class="space-y-3 mb-6">
            <div class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">1</span><p class="text-sm text-slate-700">Cliquez sur <strong>Payer par carte</strong> ci-dessous</p></div>
            <div class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">2</span><p class="text-sm text-slate-700">Renseignez vos coordonnées bancaires sur la page Stripe</p></div>
            <div class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">3</span><p class="text-sm text-slate-700">Votre forfait est activé automatiquement dès confirmation</p></div>
          </div>
          <div class="flex gap-3">
            <button type="button" onclick="goStep(1)" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">← Retour</button>
            <button type="button" id="btn-stripe-pay" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#635bff;">
              <i class="fa-solid fa-lock mr-1"></i> Payer par carte →
            </button>
          </div>
        </div>

      </div>

      
      <div id="step-3" class="p-6 hidden">

        
        <div id="status-pending" class="text-center py-4">
          <div class="relative w-20 h-20 mx-auto mb-5">
            <div class="absolute inset-0 rounded-full border-4 border-amber-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-amber-400 border-t-transparent animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center text-3xl" id="status-icon">📱</div>
          </div>
          <h3 class="font-bold text-slate-800 text-lg mb-2">En attente de confirmation</h3>
          <p class="text-slate-500 text-sm mb-1" id="status-phone-display"></p>
          <p class="text-slate-500 text-sm">Une notification a été envoyée sur votre téléphone.</p>
          <p class="text-slate-500 text-sm font-semibold">Entrez votre PIN Mobile Money pour confirmer.</p>
          <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span id="poll-status-text">Vérification en cours…</span>
          </div>
        </div>

        
        <div id="status-success" class="text-center py-4 hidden">
          <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-green-100 flex items-center justify-center">
            <i class="fa-solid fa-check text-green-500 text-3xl"></i>
          </div>
          <h3 class="font-bold text-green-700 text-lg mb-2">Paiement confirmé !</h3>
          <p class="text-slate-500 text-sm mb-4">Votre forfait <strong id="success-plan-name"></strong> est maintenant actif.</p>
          <p class="text-xs text-slate-400">Redirection dans <span id="redirect-count">3</span>s…</p>
        </div>

        
        <div id="status-failed" class="text-center py-4 hidden">
          <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-red-100 flex items-center justify-center">
            <i class="fa-solid fa-xmark text-red-500 text-3xl"></i>
          </div>
          <h3 class="font-bold text-red-700 text-lg mb-2">Paiement refusé</h3>
          <p class="text-slate-500 text-sm mb-5" id="fail-reason">Le paiement a été refusé ou a expiré.</p>
          <button type="button" onclick="goStep(2)" class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">
            ← Réessayer
          </button>
        </div>

      </div>

    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ── State ────────────────────────────────────────────────────────────────── */
let isAnnual    = false;
let md          = {};           // données du forfait courant
let pollTimer   = null;         // setInterval pour polling
let pollCount   = 0;            // nombre de polls effectués
const MAX_POLLS = 60;           // 3 min max (60 × 3s)
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';

/* ── Toggle billing (page) ────────────────────────────────────────────────── */
function toggleBilling() {
  isAnnual = !isAnnual;
  document.getElementById('billing-toggle').style.background = isAnnual ? '#2563eb' : '#e2e8f0';
  document.getElementById('toggle-dot').style.transform = isAnnual ? 'translateX(24px)' : 'translateX(0)';
  document.querySelectorAll('[id^="price-monthly-"]').forEach(el => el.classList.toggle('hidden', isAnnual));
  document.querySelectorAll('[id^="price-annual-"]').forEach(el => el.classList.toggle('hidden', !isAnnual));
}

function fmt(price, currency) {
  return price === 0 ? 'Gratuit' : price.toLocaleString('fr-FR') + ' ' + currency;
}

/* ── Ouvre le modal ───────────────────────────────────────────────────────── */
function openPayModal(planId, planName, pMonth, pAnnual, currency) {
  md = { planId, planName, pMonth, pAnnual, currency };
  stopPolling();

  document.getElementById('modal-plan-name').textContent = planName;
  document.getElementById('f-plan-id').value  = planId;
  document.getElementById('recap-plan').textContent      = planName;
  document.getElementById('opt-monthly-price').textContent = fmt(pMonth, currency) + (pMonth ? '/mois' : '');
  document.getElementById('opt-annual-price').textContent  = fmt(pAnnual, currency) + (pAnnual  ? '/an'   : '');

  const billing = isAnnual ? 'annual' : 'monthly';
  document.querySelector('input[name="billing_ui"][value="' + billing + '"]').checked = true;
  refreshRecap();
  goStep(1);
  document.getElementById('payModal').classList.replace('hidden', 'flex');
  document.body.style.overflow = 'hidden';
}

function closePayModal() {
  stopPolling();
  document.getElementById('payModal').classList.replace('flex', 'hidden');
  document.body.style.overflow = '';
}

/* ── Recap billing ────────────────────────────────────────────────────────── */
function refreshRecap() {
  const billing = document.querySelector('input[name="billing_ui"]:checked')?.value || 'monthly';
  const price   = billing === 'annual' ? md.pAnnual : md.pMonth;
  document.getElementById('recap-billing').textContent = billing === 'annual' ? 'Annuelle (–20%)' : 'Mensuelle';
  document.getElementById('recap-price').textContent   = fmt(price, md.currency) + (price ? (billing === 'monthly' ? '/mois' : '/an') : '');
  document.getElementById('lbl-monthly').style.cssText = billing === 'monthly' ? 'border-color:#f59e0b;background:#fffbeb;' : 'border-color:#e2e8f0;background:white;';
  document.getElementById('lbl-annual').style.cssText  = billing === 'annual'  ? 'border-color:#f59e0b;background:#fffbeb;' : 'border-color:#e2e8f0;background:white;';
}

function refreshMethod() {
  const sel = document.querySelector('input[name="pay_method_ui"]:checked')?.value;
  document.querySelectorAll('.pay-meth').forEach(el => {
    el.style.borderColor = el.dataset.m === sel ? '#f59e0b' : '#e2e8f0';
    el.style.background  = el.dataset.m === sel ? '#fffbeb' : 'white';
  });
}

/* ── Navigation entre étapes ─────────────────────────────────────────────── */
function goStep(n) {
  if (n === 2) {
    const method  = document.querySelector('input[name="pay_method_ui"]:checked')?.value || 'mtn_momo';
    const billing = document.querySelector('input[name="billing_ui"]:checked')?.value || 'monthly';
    const price   = billing === 'annual' ? md.pAnnual : md.pMonth;

    if (method === 'stripe') {
      document.getElementById('pay-momo').classList.add('hidden');
      document.getElementById('pay-stripe').classList.remove('hidden');
      // Bouton Stripe → soumet le formulaire fallback
      document.getElementById('btn-stripe-pay').onclick = function() {
        document.getElementById('f-method').value  = 'stripe';
        document.getElementById('f-billing').value = billing;
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Redirection…';
        document.getElementById('pay-form').submit();
      };
    } else {
      document.getElementById('pay-stripe').classList.add('hidden');
      document.getElementById('pay-momo').classList.remove('hidden');
      // Adapter selon MTN / Airtel
      document.getElementById('momo-icon').innerHTML = method === 'mtn_momo' ? `<span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#ffcc00;"><svg width="24" height="14" viewBox="0 0 40 24" fill="none"><path d="M0 0h7l5 14L17 0h7v24h-5V8l-4 11h-5L6 8v16H0V0z" fill="#000"/><path d="M25 0h15v4h-5v20h-5V4h-5V0z" fill="#000"/></svg></span>` : `<span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#e40000;"><svg width="26" height="18" viewBox="0 0 44 28" fill="none"><path d="M22 0C10 0 1 7 0 17c1-7 8-12 16-12 10 0 16 7 15 16 2-2 3-5 3-7 0-8-5-14-12-14z" fill="#fff"/><path d="M22 28C34 28 43 21 44 11c-1 7-8 12-16 12C18 23 12 16 13 7c-2 2-3 5-3 7 0 8 5 14 12 14z" fill="#fff"/></svg></span>`;
      document.getElementById('momo-title').textContent = method === 'mtn_momo' ? 'MTN Mobile Money' : 'Airtel Money';
      document.getElementById('momo-phone-hint').textContent = method === 'mtn_momo'
        ? 'Le numéro MTN MoMo enregistré sur votre téléphone.'
        : 'Le numéro Airtel Money enregistré sur votre téléphone.';
      document.getElementById('momo-amount').textContent = fmt(price, md.currency) + (price ? (billing === 'monthly' ? '/mois' : '/an') : '');
      document.getElementById('momo-error').classList.add('hidden');
      document.getElementById('inp-momo-phone').value = '';
    }
  }

  if (n === 3) {
    // Réinitialiser l'écran de statut
    document.getElementById('status-pending').classList.remove('hidden');
    document.getElementById('status-success').classList.add('hidden');
    document.getElementById('status-failed').classList.add('hidden');
  }

  for (let i = 1; i <= 3; i++) {
    document.getElementById('step-' + i).classList.toggle('hidden', i !== n);
    const t  = document.getElementById('tab-' + i);
    const tn = document.getElementById('tab-num-' + i);
    if (i === n) {
      t.style.cssText  = 'color:#ff9900;border-bottom:2px solid #ff9900;flex:1;padding:12px;text-align:center;font-size:.75rem;font-weight:600;';
      tn.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;font-size:.75rem;margin-right:4px;background:#fff3cd;color:#d97706;';
    } else if (i < n) {
      t.style.cssText  = 'color:#22c55e;border-bottom:2px solid #22c55e;flex:1;padding:12px;text-align:center;font-size:.75rem;font-weight:600;';
      tn.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;font-size:.75rem;margin-right:4px;background:#dcfce7;color:#16a34a;';
    } else {
      t.style.cssText  = 'color:#94a3b8;flex:1;padding:12px;text-align:center;font-size:.75rem;font-weight:600;';
      tn.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;font-size:.75rem;margin-right:4px;background:#f1f5f9;color:#94a3b8;';
    }
  }
}

/* ── Déclencher le push payment Mobile Money ─────────────────────────────── */
async function triggerPushPayment() {
  const phone   = document.getElementById('inp-momo-phone').value.trim();
  const method  = document.querySelector('input[name="pay_method_ui"]:checked')?.value || 'mtn_momo';
  const billing = document.querySelector('input[name="billing_ui"]:checked')?.value || 'monthly';
  const errEl   = document.getElementById('momo-error');

  if (!phone) {
    document.getElementById('inp-momo-phone').style.borderColor = '#ef4444';
    errEl.textContent = 'Veuillez saisir votre numéro de téléphone.';
    errEl.classList.remove('hidden');
    return;
  }

  const btn = document.getElementById('btn-pay-push');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Envoi en cours…';
  errEl.classList.add('hidden');

  try {
    const res = await fetch('<?php echo e(route("author.plans.pay")); ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        plan_id:        md.planId,
        billing:        billing,
        payment_method: method,
        phone:          phone
      })
    });

    const json = await res.json();

    if (!res.ok) {
      throw new Error(json.message || 'Erreur lors du déclenchement du paiement.');
    }

    // Stocker la ref pour le polling
    const authorplanId = json.authorplan_id;
    document.getElementById('f-txref').value  = authorplanId || '';
    document.getElementById('f-phone').value  = phone;
    document.getElementById('f-method').value = method;
    document.getElementById('f-billing').value = billing;

    goStep(3);
    startPolling(authorplanId);

  } catch (e) {
    errEl.textContent = e.message;
    errEl.classList.remove('hidden');
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-mobile-screen mr-1"></i> Envoyer la demande';
  }
}

/* ── Polling statut paiement ─────────────────────────────────────────────── */
function startPolling(authorplanId) {
  pollCount = 0;
  pollTimer = setInterval(async () => {
    pollCount++;
    if (pollCount > MAX_POLLS) { stopPolling(); showFail('Le délai de paiement a expiré.'); return; }

    try {
      const url  = '/author/plans/pay/' + authorplanId + '/status';
      const res  = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      const json = await res.json();
      const status = json.data?.status || json.status;

      if (status === 'SUCCESSFUL' || status === 'paid' || status === 'completed') {
        stopPolling();
        showSuccess();
      } else if (status === 'FAILED' || status === 'failed' || status === 'cancelled') {
        stopPolling();
        showFail(json.message || json.data?.reason || 'Le paiement a échoué.');
      }
    } catch (_) {}
  }, 3000);
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function showSuccess() {
  document.getElementById('step-3-pending').classList.add('hidden');
  document.getElementById('step-3-success').classList.remove('hidden');
  document.getElementById('step-3-fail').classList.add('hidden');
}

function showFail(reason) {
  document.getElementById('fail-reason').textContent = reason || 'Le paiement a été refusé ou a expiré.';
  document.getElementById('step-3-pending').classList.add('hidden');
  document.getElementById('step-3-success').classList.add('hidden');
  document.getElementById('step-3-fail').classList.remove('hidden');
}

/* ── Event listeners ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Billing radio
  document.querySelectorAll('input[name="billing_ui"]').forEach(r =>
    r.addEventListener('change', refreshRecap));

  // Payment method radio
  document.querySelectorAll('input[name="pay_method_ui"]').forEach(r =>
    r.addEventListener('change', refreshMethod));

  // Close on backdrop click
  document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closePayModal();
  });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.author', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/author/plans/index.blade.php ENDPATH**/ ?>