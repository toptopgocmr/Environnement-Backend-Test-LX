<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $__env->yieldContent('title', 'LireX Admin'); ?></title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="apple-touch-icon" href="/favicon-192.png" />
  <meta name="theme-color" content="#1a1a2e" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', sans-serif; }

    /* ── AWS-style variables ── */
    :root {
      --aws-nav:      #232f3e;
      --aws-nav2:     #16202d;
      --aws-orange:   #ff9900;
      --aws-orange-d: #e68a00;
      --aws-text:     #d1d5db;
      --aws-bg:       #f2f3f3;
    }

    body { background: var(--aws-bg); }

    /* ── Top navigation bar (AWS style) ── */
    .aws-topbar {
      background: var(--aws-nav);
      height: 48px;
      display: flex;
      align-items: center;
      padding: 0 1rem;
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 50;
      box-shadow: 0 2px 8px rgba(0,0,0,.35);
    }
    .aws-topbar-logo {
      display: flex; align-items: center; gap: 9px;
      text-decoration: none; margin-right: 24px;
    }
    .aws-topbar-logo svg { flex-shrink: 0; }
    .aws-topbar-logo .brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.25rem; font-weight: 700;
      color: #fff; line-height: 1;
    }
    .aws-topbar-logo .brand span { color: var(--aws-orange); }
    .aws-divider { width:1px; height:22px; background:rgba(255,255,255,.18); margin:0 10px; }
    .aws-nav-link {
      color: var(--aws-text);
      font-size: .8rem; font-weight: 500;
      padding: 4px 10px; border-radius: 4px;
      text-decoration: none;
      white-space: nowrap;
      transition: color .15s, background .15s;
    }
    .aws-nav-link:hover { color: #fff; background: rgba(255,255,255,.1); }
    .aws-topbar-right { margin-left: auto; display:flex; align-items:center; gap:6px; }
    .aws-icon-btn {
      color: var(--aws-text); background: none; border: none;
      padding: 6px 10px; border-radius: 4px; cursor: pointer;
      font-size: .8rem; display:flex; align-items:center; gap:5px;
      transition: color .15s, background .15s;
      position: relative;
    }
    .aws-icon-btn:hover { color: #fff; background: rgba(255,255,255,.1); }
    .notif-dot {
      position:absolute; top:4px; right:6px;
      width:7px; height:7px; border-radius:50%;
      background: var(--aws-orange);
    }

    /* ── Left sidebar (AWS service nav) ── */
    .aws-sidebar {
      width: 220px;
      background: var(--aws-nav2);
      position: fixed;
      top: 48px; left: 0; bottom: 0;
      overflow-y: auto;
      z-index: 40;
      padding: 12px 0;
      border-right: 1px solid rgba(255,255,255,.06);
    }
    .aws-sidebar::-webkit-scrollbar { width: 4px; }
    .aws-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius:2px; }

    .nav-section {
      padding: 8px 16px 4px;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: rgba(255,255,255,.35);
      margin-top: 8px;
    }
    .nav-section:first-child { margin-top: 0; }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 7px 16px;
      font-size: .8rem;
      font-weight: 500;
      color: rgba(255,255,255,.7);
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: color .15s, background .15s, border-color .15s;
      position: relative;
    }
    .nav-item i { width: 14px; text-align:center; font-size: .78rem; flex-shrink:0; }
    .nav-item:hover { color: #fff; background: rgba(255,255,255,.07); border-left-color: rgba(255,153,0,.4); }
    .nav-item.active { color: #fff; background: rgba(255,153,0,.12); border-left-color: var(--aws-orange); }
    .nav-badge {
      margin-left: auto;
      background: var(--aws-orange);
      color: #1e293b;
      font-size: .62rem;
      font-weight: 800;
      border-radius: 10px;
      padding: 1px 6px;
      line-height: 1.4;
    }
    .nav-badge-red { background: #ef4444; color: #fff; }

    /* ── Main content ── */
    .aws-main {
      margin-left: 220px;
      margin-top: 48px;
      min-height: calc(100vh - 48px);
      display: flex;
      flex-direction: column;
    }

    /* ── Breadcrumb / page header ── */
    .aws-page-header {
      background: #fff;
      border-bottom: 1px solid #d5d9d9;
      padding: 12px 24px;
    }
    .aws-breadcrumb {
      font-size: .75rem;
      color: #6b7280;
      margin-bottom: 4px;
    }
    .aws-breadcrumb a { color: #0073bb; text-decoration: none; }
    .aws-breadcrumb a:hover { text-decoration: underline; }
    .aws-page-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #16191f;
    }
    .aws-page-sub { font-size: .78rem; color: #6b7280; margin-top: 2px; }

    /* ── Flash alerts ── */
    .aws-alert-ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:6px; padding:10px 14px; font-size:.83rem; display:flex; align-items:center; gap:8px; }
    .aws-alert-err { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px; padding:10px 14px; font-size:.83rem; display:flex; align-items:center; gap:8px; }

    /* ── Cards & stat boxes (AWS info box style) ── */
    .stat-card {
      background: #fff;
      border: 1px solid #d5d9d9;
      border-radius: 6px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .stat-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }

    /* ── Badges ── */
    .badge-pending   { display:inline-flex; align-items:center; padding:1px 8px; border-radius:12px; font-size:.72rem; font-weight:600; background:#fef3c7; color:#92400e; }
    .badge-published { display:inline-flex; align-items:center; padding:1px 8px; border-radius:12px; font-size:.72rem; font-weight:600; background:#d1fae5; color:#065f46; }
    .badge-rejected  { display:inline-flex; align-items:center; padding:1px 8px; border-radius:12px; font-size:.72rem; font-weight:600; background:#fee2e2; color:#991b1b; }
    .badge-draft     { display:inline-flex; align-items:center; padding:1px 8px; border-radius:12px; font-size:.72rem; font-weight:600; background:#f1f5f9; color:#475569; }

    /* ── AWS orange button ── */
    .btn-aws {
      background: var(--aws-orange);
      color: #111;
      border: 1px solid var(--aws-orange-d);
      border-radius: 4px;
      padding: 5px 14px;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s;
    }
    .btn-aws:hover { background: var(--aws-orange-d); }
  </style>
</head>
<body class="h-full">

  
  <nav class="aws-topbar">

    
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="aws-topbar-logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2 6C2 4.9 2.9 4 4 4H11V20H4C2.9 20 2 19.1 2 18V6Z" stroke="#fff" stroke-width="1.8"/>
        <path d="M13 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H13V4Z" stroke="#ff9900" stroke-width="1.8"/>
        <line x1="12" y1="4" x2="12" y2="20" stroke="rgba(255,255,255,.4)" stroke-width="1.2"/>
      </svg>
      <span class="brand">Lire<span>X</span></span>
    </a>

    <div class="aws-divider"></div>

    <span style="color:rgba(255,255,255,.45); font-size:.72rem; margin-right:16px;">Administration</span>

    <a href="<?php echo e(route('admin.books.index')); ?>"       class="aws-nav-link">Livres</a>
    <a href="<?php echo e(route('admin.users.index')); ?>"       class="aws-nav-link">Utilisateurs</a>
    <a href="<?php echo e(route('admin.orders.index')); ?>"      class="aws-nav-link">Commandes</a>
    <a href="<?php echo e(route('admin.withdrawals.index')); ?>" class="aws-nav-link">Finance</a>
    <a href="<?php echo e(route('admin.payments.index')); ?>"    class="aws-nav-link" style="position:relative;">
      Paiements
      <?php $pendingCount = \App\Models\AuthorPlan::where('status','pending_payment')->count(); ?>
      <?php if($pendingCount > 0): ?>
      <span style="position:absolute;top:-4px;right:-8px;background:#ef4444;color:#fff;font-size:.6rem;font-weight:700;padding:1px 5px;border-radius:10px;line-height:1.4;"><?php echo e($pendingCount); ?></span>
      <?php endif; ?>
    </a>
    <a href="<?php echo e(route('admin.plans.index')); ?>"       class="aws-nav-link">Forfaits</a>

    
    <div class="aws-topbar-right">

      
      <div style="position:relative;" id="lang-wrapper-admin">
        <button onclick="toggleLang('admin')" class="aws-icon-btn" title="Langue / Language" style="gap:5px;">
          <i class="fa-solid fa-globe" style="font-size:.85rem;"></i>
          <span style="font-size:.7rem;font-weight:700;letter-spacing:.06em;background:rgba(255,255,255,.15);border-radius:3px;padding:1px 5px;">
            <?php echo e(strtoupper(session('locale', config('app.locale','fr')))); ?>

          </span>
          <i class="fa-solid fa-chevron-down" style="font-size:.55rem;opacity:.6;"></i>
        </button>
        <div id="lang-dropdown-admin" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #d5d9d9;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,.18);min-width:190px;z-index:200;max-height:340px;overflow-y:auto;">
          <div style="padding:7px 12px 5px;font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#8896a5;border-bottom:1px solid #f0f0f0;">
            🌐 Langue / Language
          </div>
          <?php
          $langs = [
            'fr'=>['flag'=>'🇫🇷','name'=>'Français',   'code'=>'FR'],
            'en'=>['flag'=>'🇬🇧','name'=>'English',    'code'=>'EN'],
            'es'=>['flag'=>'🇪🇸','name'=>'Español',    'code'=>'ES'],
            'zh'=>['flag'=>'🇨🇳','name'=>'中文',        'code'=>'ZH'],
            'pt'=>['flag'=>'🇧🇷','name'=>'Português',  'code'=>'PT'],
            'ar'=>['flag'=>'🇸🇦','name'=>'العربية',    'code'=>'AR'],
            'de'=>['flag'=>'🇩🇪','name'=>'Deutsch',    'code'=>'DE'],
            'ru'=>['flag'=>'🇷🇺','name'=>'Русский',    'code'=>'RU'],
            'ln'=>['flag'=>'🇨🇬','name'=>'Lingála',    'code'=>'LN'],
            'kt'=>['flag'=>'🇨🇬','name'=>'Kitouba',    'code'=>'KT'],
            'sw'=>['flag'=>'🇹🇿','name'=>'Kiswahili',  'code'=>'SW'],
            'ha'=>['flag'=>'🇳🇬','name'=>'Hausa',      'code'=>'HA'],
          ];
          $current = session('locale', config('app.locale','fr'));
          ?>
          <?php $__currentLoopData = $langs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('locale.set', $code)); ?>"
             style="display:flex;align-items:center;gap:9px;padding:7px 12px;font-size:.8rem;text-decoration:none;transition:background .1s;
                    <?php echo e($current === $code ? 'background:#e8f4fd;font-weight:600;' : 'color:#16191f;'); ?>">
            <span style="font-size:1rem;"><?php echo e($meta['flag']); ?></span>
            <span style="flex:1;<?php echo e($current === $code ? 'color:#0073bb;' : ''); ?>"><?php echo e($meta['name']); ?></span>
            <span style="font-size:.65rem;font-weight:700;padding:1px 5px;border-radius:3px;
                         <?php echo e($current === $code ? 'background:#0073bb;color:#fff;' : 'background:#e8eaed;color:#545b64;'); ?>">
              <?php echo e($meta['code']); ?>

            </span>
          </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <div class="aws-divider"></div>
      <button class="aws-icon-btn" title="Notifications">
        <i class="fa-solid fa-bell" style="font-size:.9rem;"></i>
        <div class="notif-dot"></div>
      </button>
      <div class="aws-divider"></div>
      <button class="aws-icon-btn" style="color:#fff; font-weight:600; font-size:.78rem; gap:5px;">
        <i class="fa-solid fa-circle-user" style="font-size:.95rem; color:var(--aws-orange);"></i>
        <?php echo e(Auth::user()->name); ?>

        <i class="fa-solid fa-chevron-down" style="font-size:.6rem;"></i>
      </button>
      <div class="aws-divider"></div>
      <a href="<?php echo e(route('admin.settings.index')); ?>" class="aws-icon-btn" title="Paramètres">
        <i class="fa-solid fa-gear"></i>
      </a>
      <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin:0;">
        <?php echo csrf_field(); ?>
        <button type="submit" class="aws-icon-btn" title="Déconnexion">
          <i class="fa-solid fa-right-from-bracket"></i>
        </button>
      </form>
    </div>
  </nav>

  
  <aside class="aws-sidebar">

    <div class="nav-section">Principal</div>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
      <i class="fa-solid fa-gauge-high"></i> Tableau de bord
    </a>

    <div class="nav-section">Contenu</div>
    <a href="<?php echo e(route('admin.books.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.books.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-book"></i> Livres
      <?php $pending = \App\Models\Book::pending()->count(); ?>
      <?php if($pending > 0): ?><span class="nav-badge"><?php echo e($pending); ?></span><?php endif; ?>
    </a>
    <a href="<?php echo e(route('admin.ai-reviews.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.ai-reviews.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-robot"></i> Analyses IA
    </a>
    <a href="<?php echo e(route('admin.categories.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-tags"></i> Catégories
    </a>

    <div class="nav-section">Communauté</div>
    <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-users"></i> Utilisateurs
    </a>
    <a href="<?php echo e(route('admin.accounts.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.accounts.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-user-check"></i> Demandes de compte
      <?php $pendingAcc = \App\Models\AccountRequest::where('status','pending')->count(); ?>
      <?php if($pendingAcc > 0): ?><span class="nav-badge"><?php echo e($pendingAcc); ?></span><?php endif; ?>
    </a>
    <a href="<?php echo e(route('admin.chat.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.chat.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-comments"></i> Messagerie
    </a>

    <div class="nav-section">Finance</div>
    <a href="<?php echo e(route('admin.orders.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.orders.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-cart-shopping"></i> Commandes
    </a>
    <a href="<?php echo e(route('admin.physical.orders')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.physical.orders') ? 'active' : ''); ?>">
      <i class="fa-solid fa-truck"></i> Commandes physiques
    </a>
    <a href="<?php echo e(route('admin.physical.stock')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.physical.stock') ? 'active' : ''); ?>">
      <i class="fa-solid fa-boxes-stacked"></i> Stock physique
    </a>
    <a href="<?php echo e(route('admin.shipping-rates.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.shipping-rates.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-map-location-dot"></i> Frais de livraison
    </a>
    <a href="<?php echo e(route('admin.withdrawals.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.withdrawals.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-money-bill-transfer"></i> Retraits
      <?php $pendingW = \App\Models\WithdrawalRequest::where('status','pending')->count(); ?>
      <?php if($pendingW > 0): ?><span class="nav-badge nav-badge-red"><?php echo e($pendingW); ?></span><?php endif; ?>
    </a>

    <div class="nav-section">Offres</div>
    <a href="<?php echo e(route('admin.plans.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.plans.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-crown"></i> Forfaits
    </a>

    <div class="nav-section">Système</div>
    <a href="<?php echo e(route('admin.settings.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
      <i class="fa-solid fa-gear"></i> Paramètres
    </a>

    
    <div style="margin-top:24px; padding:12px 16px; border-top:1px solid rgba(255,255,255,.08);">
      <div style="display:flex; align-items:center; gap:9px;">
        <img src="<?php echo e(Auth::user()->avatar_url); ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid var(--aws-orange);" alt="">
        <div style="min-width:0;">
          <p style="color:#fff; font-size:.78rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo e(Auth::user()->name); ?></p>
          <p style="color:rgba(255,255,255,.4); font-size:.66rem;">Administrateur</p>
        </div>
      </div>
    </div>
  </aside>

  
  <div class="aws-main">

    
    <div class="aws-page-header">
      <div class="aws-breadcrumb">
        <a href="<?php echo e(route('admin.dashboard')); ?>">LireX</a>
        <span style="margin:0 6px;">›</span>
        <span><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></span>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="aws-page-title"><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></h1>
          <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?>
            <p class="aws-page-sub"><?php echo $__env->yieldContent('page-subtitle'); ?></p>
          <?php endif; ?>
        </div>
        <?php if (! empty(trim($__env->yieldContent('page-actions')))): ?>
          <div><?php echo $__env->yieldContent('page-actions'); ?></div>
        <?php endif; ?>
      </div>
    </div>

    
    <div style="padding:12px 24px 0;">
      <?php if(session('success')): ?>
        <div class="aws-alert-ok mb-3">
          <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i>
          <?php echo e(session('success')); ?>

        </div>
      <?php endif; ?>
      <?php if(session('error')): ?>
        <div class="aws-alert-err mb-3">
          <i class="fa-solid fa-circle-xmark" style="color:#dc2626;"></i>
          <?php echo e(session('error')); ?>

        </div>
      <?php endif; ?>
    </div>

    
    <main style="flex:1; padding:20px 24px;">
      <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <footer style="padding:14px 24px; border-top:1px solid #d5d9d9; background:#fff; font-size:.72rem; color:#6b7280; display:flex; align-items:center; justify-content:space-between;">
      <span>© <?php echo e(date('Y')); ?> LireX — Administration &nbsp;·&nbsp; Développé avec ❤ par <strong style="color:#374151;">Basile Marius NGASSAKI ZONI</strong></span>
    </footer>
  </div>

<?php echo $__env->yieldPushContent('scripts'); ?>
<script>
function toggleLang(id) {
  const dd = document.getElementById('lang-dropdown-' + id);
  dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
}
document.addEventListener('click', function(e) {
  ['admin'].forEach(function(id) {
    const wrapper = document.getElementById('lang-wrapper-' + id);
    const dd = document.getElementById('lang-dropdown-' + id);
    if (wrapper && dd && !wrapper.contains(e.target)) dd.style.display = 'none';
  });
});
</script>
</body>
</html>
<?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/layouts/admin.blade.php ENDPATH**/ ?>