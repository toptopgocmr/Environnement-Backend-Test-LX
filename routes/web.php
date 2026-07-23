<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController as WebAuth};
use App\Http\Controllers\Admin;
use App\Http\Controllers\Author;

// ─── Locale switcher ─────────────────────────────────────────────────────────
Route::get('/locale/{code}', function (string $code) {
    $supported = ['fr','en','es','zh','pt','ar','ln','sw','de','ha','kt','ru'];
    if (in_array($code, $supported)) {
        session(['locale' => $code]);
    }
    return redirect()->back();
})->name('locale.set');

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [WebAuth::class, 'showLogin'])->name('login');
    Route::post('/login',   [WebAuth::class, 'login'])->name('login.post');
    Route::get('/register', [WebAuth::class, 'showRegister'])->name('register');
    Route::post('/register',[WebAuth::class, 'register'])->name('register.post');
    Route::get('/forgot-password',    [WebAuth::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password',   [WebAuth::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [WebAuth::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',    [WebAuth::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [WebAuth::class, 'logout'])->name('logout')->middleware('auth');

// ─── Admin ───────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Livres
    Route::prefix('books')->name('books.')->group(function () {
        Route::get('/',                  [Admin\BookController::class, 'index'])->name('index');
        Route::get('/{book}',            [Admin\BookController::class, 'show'])->name('show');
        Route::post('/{book}/approve',        [Admin\BookController::class, 'approve'])->name('approve');
        Route::post('/{book}/reject',         [Admin\BookController::class, 'reject'])->name('reject');
        Route::post('/{book}/featured',       [Admin\BookController::class, 'toggleFeatured'])->name('featured');
        Route::post('/{book}/update-cover',   [Admin\BookController::class, 'updateCover'])->name('update-cover');
        Route::post('/{book}/update-info',    [Admin\BookController::class, 'updateInfo'])->name('update-info');
        Route::delete('/{book}',              [Admin\BookController::class, 'destroy'])->name('destroy');
        // Analyse IA
        Route::post('/{book}/ai-analyze',[Admin\AiReviewController::class, 'analyze'])->name('ai-analyze');
        Route::post('/{book}/approve-ai',[Admin\AiReviewController::class, 'approveWithAi'])->name('approve-ai');
    });

    // Rapports IA
    Route::prefix('ai-reviews')->name('ai-reviews.')->group(function () {
        Route::get('/',        [Admin\AiReviewController::class, 'index'])->name('index');
        Route::get('/{aiReview}', [Admin\AiReviewController::class, 'show'])->name('show');
    });

    // Demandes d'activation
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/',                          [Admin\AccountRequestAdminController::class, 'index'])->name('index');
        Route::get('/{accountRequest}',          [Admin\AccountRequestAdminController::class, 'show'])->name('show');
        Route::post('/{accountRequest}/approve', [Admin\AccountRequestAdminController::class, 'approve'])->name('approve');
        Route::post('/{accountRequest}/reject',  [Admin\AccountRequestAdminController::class, 'reject'])->name('reject');
    });

    // Utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                        [Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}',                  [Admin\UserController::class, 'show'])->name('show');
        Route::post('/{user}/toggle-active',   [Admin\UserController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{user}/verify-author',   [Admin\UserController::class, 'verifyAuthor'])->name('verify-author');
        Route::post('/{user}/update-bio',      [Admin\UserController::class, 'updateBio'])->name('update-bio');
        Route::delete('/{user}',               [Admin\UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/chat',            [Admin\AdminChatController::class, 'startWithAuthor'])->name('chat-author');
        Route::post('/{user}/chat-reader',     [Admin\AdminChatController::class, 'startWithReader'])->name('chat-reader');
    });

    // Commandes digitales
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [Admin\OrderController::class, 'index'])->name('index');
    });

    // Commandes physiques
    Route::prefix('physical')->name('physical.')->group(function () {
        Route::get('/orders',                       [Admin\PhysicalOrderAdminController::class, 'index'])->name('orders');
        Route::get('/orders/{order}',               [Admin\PhysicalOrderAdminController::class, 'show'])->name('order-detail');
        Route::post('/orders/{order}/shipping',     [Admin\PhysicalOrderAdminController::class, 'updateShipping'])->name('shipping');
        Route::post('/orders/{order}/event',        [Admin\PhysicalOrderAdminController::class, 'addEvent'])->name('add-event');
        Route::get('/stock',                        [Admin\PhysicalOrderAdminController::class, 'stock'])->name('stock');
        Route::post('/stock/{book}',                [Admin\PhysicalOrderAdminController::class, 'addStock'])->name('add-stock');
    });

    // Retraits
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/',                       [Admin\WithdrawalController::class, 'index'])->name('index');
        Route::post('/{withdrawal}/approve',  [Admin\WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject',   [Admin\WithdrawalController::class, 'reject'])->name('reject');
    });

    // Chat admin
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/',                          [Admin\AdminChatController::class, 'index'])->name('index');
        Route::get('/{conversation}',            [Admin\AdminChatController::class, 'show'])->name('show');
        Route::get('/{conversation}/messages',   [Admin\AdminChatController::class, 'pollMessages'])->name('poll');
        Route::post('/{conversation}/message',   [Admin\AdminChatController::class, 'sendMessage'])->name('message');
        Route::post('/{conversation}/close',     [Admin\AdminChatController::class, 'close'])->name('close');
    });

    // Catégories
    Route::resource('categories', Admin\CategoryController::class)->except(['create','edit','show']);

    // Paramètres
    Route::get('/settings',  [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Frais de livraison
    Route::prefix('shipping-rates')->name('shipping-rates.')->group(function () {
        Route::get('/',           [Admin\ShippingRateController::class, 'index'])->name('index');
        Route::post('/{rate}',    [Admin\ShippingRateController::class, 'update'])->name('update');
        Route::post('/',          [Admin\ShippingRateController::class, 'store'])->name('store');
        Route::delete('/{rate}',  [Admin\ShippingRateController::class, 'destroy'])->name('destroy');
    });

    // Forfaits
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/',              [Admin\PlanController::class, 'index'])->name('index');
        Route::get('/create',        [Admin\PlanController::class, 'create'])->name('create');
        Route::post('/',             [Admin\PlanController::class, 'store'])->name('store');
        Route::get('/{plan}/edit',   [Admin\PlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}',        [Admin\PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}',     [Admin\PlanController::class, 'destroy'])->name('destroy');
        Route::post('/{plan}/toggle',[Admin\PlanController::class, 'toggleActive'])->name('toggle');
    });

    // Paiements en attente
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                            [Admin\PlanController::class, 'payments'])->name('index');
        Route::post('/{authorPlan}/approve',       [Admin\PlanController::class, 'approvePayment'])->name('approve');
        Route::post('/{authorPlan}/reject',        [Admin\PlanController::class, 'rejectPayment'])->name('reject');
    });
});

// ─── Auteur ──────────────────────────────────────────────────────────────────
Route::prefix('author')->name('author.')->middleware(['auth','role:author,admin'])->group(function () {
    Route::get('/', [Author\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('books')->name('books.')->group(function () {
        Route::get('/',           [Author\BookController::class, 'index'])->name('index');
        Route::get('/create',     [Author\BookController::class, 'create'])->name('create');
        Route::post('/',          [Author\BookController::class, 'store'])->name('store');
        Route::get('/{book}',     [Author\BookController::class, 'show'])->name('show');
        Route::get('/{book}/edit',[Author\BookController::class, 'edit'])->name('edit');
        Route::put('/{book}',     [Author\BookController::class, 'update'])->name('update');
        Route::delete('/{book}',  [Author\BookController::class, 'destroy'])->name('destroy');
        Route::get('/{book}/stats',[Author\BookController::class, 'stats'])->name('stats');
    });

    // Clients
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [Author\CustomerController::class, 'index'])->name('index');
    });

    // Revenus
    Route::prefix('earnings')->name('earnings.')->group(function () {
        Route::get('/',    [Author\EarningsController::class, 'index'])->name('index');
        Route::post('/withdraw', [Author\EarningsController::class, 'withdraw'])->name('withdraw');
    });

    // Forfaits auteur
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/',          [Author\PlanController::class, 'index'])->name('index');
        Route::post('/subscribe',[Author\PlanController::class, 'subscribe'])->name('subscribe');
        Route::post('/pay',      [Author\PlanController::class, 'initiatePayment'])->name('pay');
        Route::get('/pay/{authorPlan}/status', [Author\PlanController::class, 'checkStatus'])->name('pay.status');
    });

    // Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit',  [Author\ProfileController::class, 'edit'])->name('edit');
        Route::put('/',      [Author\ProfileController::class, 'update'])->name('update');
    });

    // Chat auteur (admin + clients)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/',                          [Author\AuthorChatController::class, 'index'])->name('index');
        Route::get('/{conversation}',            [Author\AuthorChatController::class, 'show'])->name('show');
        Route::get('/{conversation}/messages',   [Author\AuthorChatController::class, 'pollMessages'])->name('poll');
        Route::post('/{conversation}/message',   [Author\AuthorChatController::class, 'sendMessage'])->name('message');
        Route::post('/start-admin',              [Author\AuthorChatController::class, 'startWithAdmin'])->name('start-admin');
    });
});
