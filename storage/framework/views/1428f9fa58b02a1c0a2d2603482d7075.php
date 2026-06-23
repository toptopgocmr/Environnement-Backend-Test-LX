<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"/>
  <title><?php echo $__env->yieldContent('title', 'LireX – Espace Auteur'); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', sans-serif; }
    :root {
      --aws-nav:    #232f3e;
      --aws-nav2:   #16202d;
      --aws-orange: #ff9900;
      --aws-bg:     #f2f3f3;
    }
    body { background: var(--aws-bg); }

    /* ── Top bar ── */
    .aws-topbar { background:var(--aws-nav); height:48px; display:flex; align-items:center; padding:0 1rem; position:fixed; top:0; left:0; right:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,.35); }
    .aws-topbar-logo { display:flex; align-items:center; gap:9px; text-decoration:none; margin-right:20px; }
    .aws-topbar-logo .brand { font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:700; color:#fff; }
    .aws-topbar-logo .brand span { color:var(--aws-orange); }
    .aws-divider { width:1px; height:22px; background:rgba(255,255,255,.18); margin:0 10px; }
    .aws-nav-link { color:rgba(255,255,255,.7); font-size:.8rem; font-weight:500; padding:4px 10px; border-radius:4px; text-decoration:none; white-space:nowrap; transition:color .15s,background .15s; }
    .aws-nav-link:hover { color:#fff; background:rgba(255,255,255,.1); }
    .aws-topbar-right { margin-left:auto; display:flex; align-items:center; gap:4px; }
    .aws-icon-btn { color:rgba(255,255,255,.7); background:none; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:.8rem; display:flex; align-items:center; gap:5px; transition:color .15s,background .15s; text-decoration:none; }
    .aws-icon-btn:hover { color:#fff; background:rgba(255,255,255,.1); }

    /* ── Sidebar ── */
    .aws-sidebar { width:210px; background:var(--aws-nav2); position:fixed; top:48px; left:0; bottom:0; overflow-y:auto; z-index:40; padding:10px 0; border-right:1px solid rgba(255,255,255,.06); }
    .aws-sidebar::-webkit-scrollbar { width:3px; }
    .aws-sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1); border-radius:2px; }
    .nav-section { padding:8px 16px 3px; font-size:.65rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.3); margin-top:6px; }
    .nav-section:first-child { margin-top:0; }
    .nav-item { display:flex; align-items:center; gap:9px; padding:7px 16px; font-size:.8rem; font-weight:500; color:rgba(255,255,255,.65); text-decoration:none; border-left:3px solid transparent; transition:color .15s,background .15s,border-color .15s; position:relative; }
    .nav-item i { width:14px; text-align:center; font-size:.77rem; flex-shrink:0; }
    .nav-item:hover { color:#fff; background:rgba(255,255,255,.07); border-left-color:rgba(255,153,0,.4); }
    .nav-item.active { color:#fff; background:rgba(255,153,0,.12); border-left-color:var(--aws-orange); }
    .nav-badge { margin-left:auto; background:var(--aws-orange); color:#111; font-size:.6rem; font-weight:800; border-radius:10px; padding:1px 5px; min-width:16px; text-align:center; }
    .nav-badge-red { margin-left:auto; background:#ef4444; color:#fff; font-size:.6rem; font-weight:800; border-radius:10px; padding:1px 5px; min-width:16px; text-align:center; }

    /* ── Main ── */
    .aws-main { margin-left:210px; margin-top:48px; min-height:calc(100vh - 48px); display:flex; flex-direction:column; }

    /* ── Page header ── */
    .aws-page-header { background:#fff; border-bottom:1px solid #d5d9d9; padding:12px 24px; }
    .aws-breadcrumb { font-size:.75rem; color:#6b7280; margin-bottom:3px; }
    .aws-breadcrumb a { color:#0073bb; text-decoration:none; }
    .aws-breadcrumb a:hover { text-decoration:underline; }
    .aws-page-title { font-size:1.1rem; font-weight:700; color:#16191f; }
    .aws-page-sub { font-size:.77rem; color:#6b7280; margin-top:2px; }

    /* ── Alerts ── */
    .aws-alert-ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:6px; padding:10px 14px; font-size:.82rem; display:flex; align-items:center; gap:8px; }
    .aws-alert-err { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px; padding:10px 14px; font-size:.82rem; display:flex; align-items:center; gap:8px; }

    /* ── Cards ── */
    .stat-card { background:#fff; border:1px solid #d5d9d9; border-radius:6px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .stat-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.1); }

    /* ── Badges ── */
    .badge-pub      { display:inline-flex; align-items:center; padding:2px 8px; border-radius:12px; font-size:.7rem; font-weight:600; background:#d1fae5; color:#065f46; }
    .badge-draft    { display:inline-flex; align-items:center; padding:2px 8px; border-radius:12px; font-size:.7rem; font-weight:600; background:#e0e7ff; color:#3730a3; }
    .badge-pending  { display:inline-flex; align-items:center; padding:2px 8px; border-radius:12px; font-size:.7rem; font-weight:600; background:#fef3c7; color:#92400e; }
    .badge-rejected { display:inline-flex; align-items:center; padding:2px 8px; border-radius:12px; font-size:.7rem; font-weight:600; background:#fee2e2; color:#991b1b; }

    /* ── Tables ── */
    .aws-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .aws-table th { background:#f8f8f8; border-bottom:2px solid #d5d9d9; padding:9px 14px; text-align:left; font-size:.72rem; font-weight:700; color:#545b64; text-transform:uppercase; letter-spacing:.04em; }
    .aws-table td { border-bottom:1px solid #f0f0f0; padding:10px 14px; color:#16191f; vertical-align:middle; }
    .aws-table tr:hover td { background:#fafafa; }

    /* ── Buttons ── */
    .btn-primary { background:#0073bb; color:#fff; border:none; padding:7px 16px; border-radius:5px; font-size:.82rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
    .btn-primary:hover { background:#005a94; }
    .btn-orange { background:#ff9900; color:#232f3e; border:none; padding:7px 16px; border-radius:5px; font-size:.82rem; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
    .btn-orange:hover { background:#e88900; }
    .btn-ghost { background:transparent; color:#0073bb; border:1px solid #0073bb; padding:6px 14px; border-radius:5px; font-size:.82rem; font-weight:500; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all .15s; }
    .btn-ghost:hover { background:#0073bb; color:#fff; }

    /* ── User dropdown ── */
    #user-dropdown { display:none; position:absolute; right:0; top:calc(100% + 6px); background:#fff; border:1px solid #d5d9d9; border-radius:6px; box-shadow:0 4px 16px rgba(0,0,0,.15); min-width:200px; z-index:100; }
    #user-dropdown.open { display:block; }
    .dd-item { display:flex; align-items:center; gap:9px; padding:9px 14px; font-size:.8rem; color:#16191f; text-decoration:none; transition:background .1s; }
    .dd-item:hover { background:#f2f3f3; }
    .dd-item i { width:14px; color:#545b64; }
    .dd-sep { border:none; border-top:1px solid #f0f0f0; margin:4px 0; }

    <?php echo $__env->yieldContent('styles'); ?>
  </style>
  <?php echo $__env->yieldContent('head'); ?>
</head>
<body class="h-full">


<header class="aws-topbar">

  
  <a href="<?php echo e(route('author.dashboard')); ?>" class="aws-topbar-logo">
    <svg width="28" height="24" viewBox="0 0 36 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M2 3C2 1.9 2.9 1 4 1H17V31H4C2.9 31 2 30.1 2 29V3Z" stroke="#4a9eff" stroke-width="1.6" fill="none"/>
      <path d="M19 1H32C33.1 1 34 1.9 34 3V29C34 30.1 33.1 31 32 31H19V1Z" stroke="#4a9eff" stroke-width="1.6" fill="none"/>
      <line x1="18" y1="1" x2="18" y2="31" stroke="#6bb5ff" stroke-width="1.2"/>
      <line x1="6" y1="10" x2="14" y2="10" stroke="#4a9eff" stroke-width="1.2" stroke-linecap="round"/>
      <line x1="6" y1="14" x2="14" y2="14" stroke="#4a9eff" stroke-width="1.2" stroke-linecap="round"/>
      <line x1="6" y1="18" x2="11" y2="18" stroke="#4a9eff" stroke-width="1.2" stroke-linecap="round"/>
      <line x1="23" y1="10" x2="31" y2="22" stroke="#4a9eff" stroke-width="1.6" stroke-linecap="round"/>
      <line x1="31" y1="10" x2="23" y2="22" stroke="#4a9eff" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
    <span class="brand">Lire<span>X</span></span>
  </a>

  <div class="aws-divider"></div>
  <span class="aws-nav-link" style="color:rgba(255,255,255,.5); cursor:default;">Espace Auteur</span>
  <div class="aws-divider"></div>
  <a href="<?php echo e(route('author.books.index')); ?>"   class="aws-nav-link">Mes livres</a>
  <a href="<?php echo e(route('author.customers.index')); ?>" class="aws-nav-link">Clients</a>
  <a href="<?php echo e(route('author.earnings.index')); ?>" class="aws-nav-link">Revenus</a>
  <a href="<?php echo e(route('author.plans.index')); ?>"    class="aws-nav-link">Forfaits</a>

  
  <div class="aws-topbar-right">
    
    <?php $unreadCount = \App\Models\ChatConversation::whereHas('participants', fn($q) => $q->where('user_id', auth()->id()))->withCount(['messages as unread' => fn($q) => $q->where('sender_id','!=',auth()->id())->where('is_read',false)])->get()->sum('unread'); ?>
    <a href="<?php echo e(route('author.chat.index')); ?>" class="aws-icon-btn" style="position:relative;">
      <i class="far fa-comment-dots"></i>
      <?php if($unreadCount > 0): ?>
        <span style="position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;font-size:.55rem;font-weight:800;border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center;"><?php echo e($unreadCount > 9 ? '9+' : $unreadCount); ?></span>
      <?php endif; ?>
    </a>

    
    <div style="position:relative;" id="lang-wrapper-author">
      <button onclick="toggleLang('author')" class="aws-icon-btn" title="Langue / Language" style="gap:5px;">
        <i class="fa-solid fa-globe" style="font-size:.85rem;"></i>
        <span style="font-size:.7rem;font-weight:700;letter-spacing:.06em;background:rgba(255,255,255,.15);border-radius:3px;padding:1px 5px;">
          <?php echo e(strtoupper(session('locale', config('app.locale','fr')))); ?>

        </span>
        <i class="fa-solid fa-chevron-down" style="font-size:.55rem;opacity:.6;"></i>
      </button>
      <div id="lang-dropdown-author" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #d5d9d9;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,.18);min-width:190px;z-index:200;max-height:340px;overflow-y:auto;">
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

    
    <div style="position:relative;">
      <button onclick="toggleDropdown()" class="aws-icon-btn" style="gap:7px;">
        <div style="width:26px;height:26px;border-radius:50%;background:#ff9900;display:flex;align-items:center;justify-content:center;color:#232f3e;font-weight:800;font-size:.75rem;">
          <?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?>

        </div>
        <span style="color:#fff;font-size:.8rem;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e(auth()->user()->name); ?></span>
        <i class="fas fa-chevron-down" style="font-size:.6rem;opacity:.6;"></i>
      </button>
      <div id="user-dropdown">
        <div style="padding:10px 14px;border-bottom:1px solid #f0f0f0;">
          <p style="font-size:.82rem;font-weight:700;color:#16191f;"><?php echo e(auth()->user()->name); ?></p>
          <p style="font-size:.72rem;color:#545b64;"><?php echo e(auth()->user()->email); ?></p>
        </div>
        <a href="<?php echo e(route('author.profile.edit')); ?>" class="dd-item"><i class="far fa-user"></i> Mon profil</a>
        <a href="<?php echo e(route('author.chat.index')); ?>"   class="dd-item"><i class="far fa-comment-dots"></i> Messages</a>
        <a href="<?php echo e(route('author.plans.index')); ?>"  class="dd-item"><i class="fas fa-crown" style="color:#ff9900;"></i> Mon forfait</a>
        <hr class="dd-sep">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit" class="dd-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;color:#d13212;">
            <i class="fas fa-sign-out-alt"></i> Se déconnecter
          </button>
        </form>
      </div>
    </div>
  </div>
</header>


<aside class="aws-sidebar">

  <div class="nav-section">Principal</div>
  <a href="<?php echo e(route('author.dashboard')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.dashboard') ? 'active' : ''); ?>">
    <i class="fas fa-th-large"></i> Tableau de bord
  </a>

  <div class="nav-section">Mes livres</div>
  <a href="<?php echo e(route('author.books.index')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.books.*') ? 'active' : ''); ?>">
    <i class="fas fa-book"></i> Tous mes livres
  </a>
  <a href="<?php echo e(route('author.books.create')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.books.create') ? 'active' : ''); ?>">
    <i class="fas fa-plus-circle"></i> Publier un livre
  </a>

  <div class="nav-section">Communauté</div>
  <a href="<?php echo e(route('author.customers.index')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.customers.*') ? 'active' : ''); ?>">
    <i class="fas fa-users"></i> Mes Clients
  </a>
  <?php
    $chatUnread = \App\Models\ChatConversation::whereHas('participants', fn($q) => $q->where('user_id', auth()->id()))
      ->withCount(['messages as unread' => fn($q) => $q->where('sender_id','!=',auth()->id())->where('is_read',false)])
      ->get()->sum('unread');
  ?>
  <a href="<?php echo e(route('author.chat.index')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.chat.*') ? 'active' : ''); ?>">
    <i class="far fa-comment-dots"></i> Messages
    <?php if($chatUnread > 0): ?>
      <span class="nav-badge-red"><?php echo e($chatUnread > 9 ? '9+' : $chatUnread); ?></span>
    <?php endif; ?>
  </a>

  <div class="nav-section">Finance</div>
  <a href="<?php echo e(route('author.earnings.index')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.earnings.*') ? 'active' : ''); ?>">
    <i class="fas fa-chart-line"></i> Revenus &amp; Retraits
  </a>

  <div class="nav-section">Abonnement</div>
  <a href="<?php echo e(route('author.plans.index')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.plans.*') ? 'active' : ''); ?>">
    <i class="fas fa-crown" style="color:#ff9900;"></i> Mes Forfaits
  </a>

  <div class="nav-section">Compte</div>
  <a href="<?php echo e(route('author.profile.edit')); ?>"
     class="nav-item <?php echo e(request()->routeIs('author.profile.*') ? 'active' : ''); ?>">
    <i class="far fa-user-circle"></i> Mon Profil
  </a>

  
  <div style="margin-top:auto; padding:16px; border-top:1px solid rgba(255,255,255,.06); margin-top:20px;">
    <p style="font-size:.65rem; color:rgba(255,255,255,.25); line-height:1.4;">
      &copy; <?php echo e(date('Y')); ?> LireX<br>
      <?php if(auth()->user()->currentPlan): ?>
        <span style="color:#ff9900;"><?php echo e(auth()->user()->currentPlan->plan->name ?? 'Plan actif'); ?></span>
      <?php endif; ?>
    </p>
  </div>
</aside>


<main class="aws-main">

  
  <div class="aws-page-header">
    <div class="aws-breadcrumb">
      <a href="<?php echo e(route('author.dashboard')); ?>">LireX</a> › Espace Auteur
      <?php if (! empty(trim($__env->yieldContent('breadcrumb')))): ?> › <?php echo $__env->yieldContent('breadcrumb'); ?> <?php endif; ?>
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between;">
      <div>
        <h1 class="aws-page-title"><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></h1>
        <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?><p class="aws-page-sub"><?php echo $__env->yieldContent('page-subtitle'); ?></p><?php endif; ?>
      </div>
      <?php if (! empty(trim($__env->yieldContent('page-actions')))): ?><div><?php echo $__env->yieldContent('page-actions'); ?></div><?php endif; ?>
    </div>
  </div>

  
  <div style="padding:0 24px; margin-top:12px;">
    <?php if(session('success')): ?>
      <div class="aws-alert-ok"><i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="aws-alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
      <div class="aws-alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo e($errors->first()); ?></div>
    <?php endif; ?>
  </div>

  
  <div style="padding:20px 24px; flex:1;">
    <?php echo $__env->yieldContent('content'); ?>
  </div>

  
  <footer style="padding:12px 24px; border-top:1px solid #d5d9d9; background:#fff; font-size:.72rem; color:#545b64; display:flex; justify-content:space-between; align-items:center;">
    <span>&copy; <?php echo e(date('Y')); ?> LireX &mdash; Espace Auteur</span>
    <span>Développé avec <span style="color:#ef4444;">❤</span> par <strong style="color:#545b64;">Basile Marius NGASSAKI ZONI</strong></span>
  </footer>
</main>

<script>
function toggleDropdown() {
  const dd = document.getElementById('user-dropdown');
  dd.classList.toggle('open');
}
function toggleLang(id) {
  const dd = document.getElementById('lang-dropdown-' + id);
  dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
}
document.addEventListener('click', function(e) {
  // Close user dropdown
  const ud = document.getElementById('user-dropdown');
  if (!e.target.closest('[onclick="toggleDropdown()"]') && !e.target.closest('#user-dropdown')) {
    ud.classList.remove('open');
  }
  // Close lang dropdown
  const lw = document.getElementById('lang-wrapper-author');
  const ld = document.getElementById('lang-dropdown-author');
  if (lw && ld && !lw.contains(e.target)) ld.style.display = 'none';
});
</script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/layouts/author.blade.php ENDPATH**/ ?>