<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    AuthController, BookController, OrderController,
    CategoryController, LibraryController, AuthorApiController,
    ChatController, PublicationPlanController, AccountRequestController,
    PhysicalOrderController, CitationController, ReadingSessionController,
    TranslateController,
};

Route::prefix('v1')->group(function () {

    // ─── Public ──────────────────────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);

    Route::get('/books',                [BookController::class, 'index']);
    Route::get('/books/featured',       [BookController::class, 'featured']);
    Route::get('/books/{book}',         [BookController::class, 'show']);
    Route::get('/books/{book}/preview', [BookController::class, 'preview']);
    Route::get('/books/by-author/{author}', [BookController::class, 'byAuthor']);
    Route::get('/categories',           [CategoryController::class, 'index']);
    Route::get('/plans',                [PublicationPlanController::class, 'index']);
    Route::post('/books/{book}/citation', [CitationController::class, 'generate']);

    // Frais de livraison (public)
    Route::get('/shipping-rates', function () {
        $rates = \App\Models\ShippingRate::where('is_active', true)->orderBy('zone')->get();
        return response()->json(['success' => true, 'data' => $rates]);
    });

    // Pays où Peex peut collecter (piloté par PEEX_COLLECT_COUNTRIES, sans redeploy)
    Route::get('/payments/peex-countries', function () {
        return response()->json(['success' => true, 'data' => ['countries' => config('services.peex.collect_countries', [])]]);
    });
    Route::post('/books/{book}/translate', [TranslateController::class, 'translate']);
    Route::post('/payments/callback/{method}', [OrderController::class, 'callback'])
         ->name('api.payment.callback');

    // Streaming PDF via token temporaire (pas besoin de Bearer header, compatible iframe)
    Route::get('/books/{book}/stream/{token}', [BookController::class, 'streamBook'])
         ->name('api.books.stream');

    // Lien de lecture — public pour livres gratuits, vérifie l'achat pour les payants
    Route::get('/books/{book}/read-link', [BookController::class, 'readLink']);

    // Avis approuvés — public
    Route::get('/books/{book}/reviews', [BookController::class, 'getReviews']);

    // Assistant IA — accessible sans auth (question sur le contenu public du livre)
    Route::post('/books/{book}/ask', [BookController::class, 'ask']);

    // ─── Authentifié ─────────────────────────────────────────────────────────
    Route::middleware('auth:api')->group(function () {

        Route::post('/auth/logout',   [AuthController::class, 'logout']);
        Route::post('/auth/refresh',  [AuthController::class, 'refresh']);
        Route::get('/auth/me',        [AuthController::class, 'me']);
        Route::put('/auth/profile',   [AuthController::class, 'updateProfile']);
        Route::put('/auth/password',  [AuthController::class, 'changePassword']);

        Route::post('/account-request',     [AccountRequestController::class, 'store']);
        Route::get('/account-request/mine', [AccountRequestController::class, 'myRequest']);

        Route::post('/plans/subscribe', [PublicationPlanController::class, 'subscribe']);
        Route::get('/plans/mine',       [PublicationPlanController::class, 'myPlan']);

        Route::get('/books/{book}/download',       [BookController::class, 'download'])->name('api.books.download');
        Route::post('/books/{book}/reviews',       [BookController::class, 'storeReview']);
        Route::get('/citations',                   [CitationController::class, 'myCitations']);

        Route::post('/orders/initiate',            [OrderController::class, 'initiate']);
        Route::get('/orders',                      [OrderController::class, 'myOrders']);
        Route::post('/orders/{order}/download-link',[OrderController::class, 'downloadLink']);

        Route::post('/physical/orders',            [PhysicalOrderController::class, 'initiate']);
        Route::get('/physical/orders',             [PhysicalOrderController::class, 'myPhysicalOrders']);
        Route::get('/physical/addresses',          [PhysicalOrderController::class, 'shippingAddresses']);
        Route::post('/physical/addresses',         [PhysicalOrderController::class, 'storeAddress']);
        Route::get('/physical/orders/{order}/tracking', [PhysicalOrderController::class, 'tracking'])->name('api.tracking');

        Route::post('/books/{book}/rent',          [ReadingSessionController::class, 'rent']);
        Route::get('/reading-sessions',            [ReadingSessionController::class, 'myActiveSessions']);

        Route::get('/library',                          [LibraryController::class, 'myLibrary']);
        Route::get('/library/wishlist',                 [LibraryController::class, 'wishlist']);
        Route::post('/library/wishlist/{book}',         [LibraryController::class, 'toggleWishlist']);
        Route::delete('/library/wishlist/{book}',       [LibraryController::class, 'removeWishlist']);
        Route::get('/library/wishlist/{book}/check',    [LibraryController::class, 'checkWishlist']);
        Route::put('/library/progress/{book}',          [LibraryController::class, 'updateProgress']);

        Route::get('/books/{book}/in-library',          [LibraryController::class, 'inLibrary']);

        Route::get('/chat',                        [ChatController::class, 'index']);
        Route::get('/chat/unread',                 [ChatController::class, 'unreadCount']);
        Route::post('/chat/start-with-author',     [ChatController::class, 'startWithAuthor']);
        Route::get('/chat/{conversation}',         [ChatController::class, 'show']);
        Route::post('/chat/{conversation}/messages',[ChatController::class, 'sendMessage']);
        Route::post('/chat/{conversation}/read',   [ChatController::class, 'markRead']);

        Route::middleware('role:author,admin')->prefix('author')->group(function () {
            Route::get('/dashboard',          [AuthorApiController::class, 'dashboard']);
            Route::get('/books',              [AuthorApiController::class, 'myBooks']);
            Route::post('/books',             [AuthorApiController::class, 'submitBook']);
            Route::get('/earnings',           [AuthorApiController::class, 'earnings']);
            Route::post('/earnings/withdraw', [AuthorApiController::class, 'withdraw']);
        });
    });
});
