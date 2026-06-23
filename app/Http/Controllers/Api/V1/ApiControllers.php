<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage};
use Tymon\JWTAuth\Facades\JWTAuth;

// ═══════════════════════════════════════════════════════════
// AUTH API
// ═══════════════════════════════════════════════════════════
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user  = User::create(array_merge($data, ['role' => 'reader']));
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès.',
            'data'    => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['success' => false, 'message' => 'Identifiants incorrects.'], 401);
        }

        $user = Auth::user();
        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Compte suspendu. Contactez le support.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer', 'expires_in' => config('jwt.ttl') * 60],
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['success' => true, 'message' => 'Déconnecté.']);
    }

    public function refresh(): JsonResponse
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::user();
        $user->load('subscriptions');
        return response()->json(['success' => true, 'data' => array_merge($user->toArray(), [
            'has_active_subscription' => $user->hasActiveSubscription(),
            'pending_balance'         => $user->pending_balance,
        ])]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'   => 'sometimes|string|max:100',
            'bio'    => 'nullable|string|max:1000',
            'phone'  => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'city'   => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);
        return response()->json(['success' => true, 'data' => $user->fresh()]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Mot de passe actuel incorrect.'], 400);
        }
        Auth::user()->update(['password' => bcrypt($request->password)]);
        return response()->json(['success' => true, 'message' => 'Mot de passe modifié.']);
    }
}

// ═══════════════════════════════════════════════════════════
// BOOKS API  –  MySQL : LIKE au lieu de ILIKE
// ═══════════════════════════════════════════════════════════
class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $books = Book::published()
            ->with(['author:id,name,avatar,is_verified_author', 'category:id,name,icon,color'])
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->q, function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                // MySQL : LIKE (case-insensitive par défaut avec collation utf8mb4_unicode_ci)
                $q->where(function ($q) use ($term, $request) {
                    $q->where('title', 'LIKE', $term)
                      ->orWhere('description', 'LIKE', $term)
                      ->orWhereHas('tags', fn($q) => $q->where('tag', 'LIKE', $term))
                      ->orWhereHas('author', fn($q) => $q->where('name', 'LIKE', $term));
                });
            })
            ->when($request->language,  fn($q) => $q->where('language', $request->language))
            ->when($request->is_free,   fn($q) => $q->where('is_free', true))
            ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->sort === 'popular', fn($q) => $q->orderByDesc('downloads'))
            ->when($request->sort === 'rated',   fn($q) => $q->orderByDesc('average_rating'))
            ->when($request->sort === 'newest',  fn($q) => $q->latest())
            ->when(!$request->sort,              fn($q) => $q->latest())
            ->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $books]);
    }

    public function featured(): JsonResponse
    {
        $books = Book::published()->featured()
            ->with(['author:id,name,avatar,is_verified_author', 'category:id,name'])
            ->limit(10)->get();
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function show(Book $book): JsonResponse
    {
        if ($book->status !== 'published') abort(404);
        $book->increment('views');
        $book->load([
            'author:id,name,avatar,bio,is_verified_author',
            'category',
            'tags',
            'reviews' => fn($q) => $q->where('is_approved', true)->with('user:id,name,avatar')->latest()->limit(10),
        ]);

        $userHasPurchased = Auth::check() ? Auth::user()->hasPurchased($book->id) : false;
        $userRating       = Auth::check() ? $book->reviews()->where('user_id', Auth::id())->first() : null;

        return response()->json(['success' => true, 'data' => array_merge($book->toArray(), [
            'user_has_purchased' => $userHasPurchased,
            'user_rating'        => $userRating,
        ])]);
    }

    public function download(Book $book): mixed
    {
        $user = Auth::user();
        if (!$book->is_free && !$user->hasPurchased($book->id) && !$user->hasActiveSubscription()) {
            return response()->json(['success' => false, 'message' => 'Achat requis.'], 403);
        }
        $book->increment('downloads');
        return Storage::disk('local')->download($book->file_path, \Str::slug($book->title) . '.pdf');
    }

    public function preview(Book $book): JsonResponse
    {
        $url = $book->preview_path
            ? Storage::temporaryUrl('local/' . $book->preview_path, now()->addHour())
            : null;
        return response()->json(['success' => true, 'data' => ['preview_url' => $url]]);
    }

    public function byAuthor(User $author): JsonResponse
    {
        $books = $author->books()->published()->with('category:id,name')->latest()->paginate(12);
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function storeReview(Request $request, Book $book): JsonResponse
    {
        if (!Auth::user()->hasPurchased($book->id)) {
            return response()->json(['success' => false, 'message' => 'Achat requis pour laisser un avis.'], 403);
        }
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        Review::updateOrCreate(
            ['book_id' => $book->id, 'user_id' => Auth::id()],
            array_merge($data, ['is_approved' => false])
        );
        // Recalcul note moyenne
        $avg   = $book->reviews()->where('is_approved', true)->avg('rating');
        $count = $book->reviews()->where('is_approved', true)->count();
        $book->update(['average_rating' => round($avg ?? 0, 2), 'ratings_count' => $count]);

        return response()->json(['success' => true, 'message' => 'Avis soumis pour modération.']);
    }
}

// ═══════════════════════════════════════════════════════════
// ORDERS / PAYMENT API
// ═══════════════════════════════════════════════════════════
class OrderController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function initiate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id'        => 'required|exists:books,id',
            'payment_method' => 'required|in:mtn_momo,airtel_money,stripe,free',
            'phone'          => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string|max:20',
            'type'           => 'in:digital,print',
        ]);

        $book = Book::findOrFail($data['book_id']);
        $user = Auth::user();

        if ($user->hasPurchased($book->id)) {
            return response()->json(['success' => false, 'message' => 'Vous possédez déjà ce livre.'], 400);
        }

        if ($book->is_free || $data['payment_method'] === 'free') {
            $order = Order::create([
                'reference'      => 'LRX' . strtoupper(\Str::random(7)),
                'user_id'        => $user->id,
                'book_id'        => $book->id,
                'amount'         => 0,
                'currency'       => $book->currency,
                'type'           => 'digital',
                'payment_method' => 'free',
                'payment_status' => 'paid',
                'download_token' => \Str::random(64),
                'max_downloads'  => 3,
                'expires_at'     => now()->addYears(99),
            ]);
            return response()->json(['success' => true, 'data' => ['order' => $order]]);
        }

        $result = $this->paymentService->initiate($data, $book, $user);
        return response()->json(['success' => $result['success'], 'data' => $result]);
    }

    public function callback(Request $request, string $method): JsonResponse
    {
        $result = $this->paymentService->handleCallback($method, $request->all());
        return response()->json($result);
    }

    public function myOrders(): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->with(['book:id,title,cover_image,author_id', 'book.author:id,name'])
            ->where('payment_status', 'paid')
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function downloadLink(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id()) abort(403);
        if ($order->download_count >= $order->max_downloads) {
            return response()->json(['success' => false, 'message' => 'Limite de téléchargements atteinte.'], 403);
        }
        $order->increment('download_count');
        $url = route('api.books.download', $order->book_id) . '?token=' . $order->download_token;
        return response()->json(['success' => true, 'data' => ['download_url' => $url]]);
    }
}

// ═══════════════════════════════════════════════════════════
// CATEGORIES API
// ═══════════════════════════════════════════════════════════
class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $cats = Category::where('is_active', true)
            ->withCount('books')
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $cats]);
    }
}

// ═══════════════════════════════════════════════════════════
// LIBRARY / WISHLIST / PROGRESS
// ═══════════════════════════════════════════════════════════
class LibraryController extends Controller
{
    public function myLibrary(): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->where('payment_status', 'paid')
            ->with(['book:id,title,cover_image,author_id,pages,language,format', 'book.author:id,name'])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function wishlist(): JsonResponse
    {
        $books = Auth::user()->wishlists()
            ->with(['author:id,name', 'category:id,name'])
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function toggleWishlist(Book $book): JsonResponse
    {
        $user = Auth::user();
        if ($user->wishlists()->where('book_id', $book->id)->exists()) {
            $user->wishlists()->detach($book->id);
            $msg = 'Retiré de la liste de souhaits.';
        } else {
            $user->wishlists()->attach($book->id);
            $msg = 'Ajouté à la liste de souhaits.';
        }
        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function updateProgress(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'current_page' => 'required|integer|min:1',
            'total_pages'  => 'required|integer|min:1',
        ]);
        $percentage = round(($data['current_page'] / $data['total_pages']) * 100, 2);

        ReadingProgress::updateOrCreate(
            ['user_id' => Auth::id(), 'book_id' => $book->id],
            array_merge($data, ['percentage' => $percentage, 'last_read_at' => now()])
        );
        return response()->json(['success' => true, 'data' => ['percentage' => $percentage]]);
    }
}

// ═══════════════════════════════════════════════════════════
// AUTHOR API
// ═══════════════════════════════════════════════════════════
class AuthorApiController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $author = Auth::user();
        return response()->json(['success' => true, 'data' => [
            'stats' => [
                'books'           => $author->books()->count(),
                'published'       => $author->books()->where('status', 'published')->count(),
                'total_sales'     => Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))->where('payment_status', 'paid')->count(),
                'pending_balance' => $author->pending_balance,
                'total_earned'    => $author->total_earnings,
                'followers'       => $author->followers()->count(),
            ],
        ]]);
    }

    public function myBooks(): JsonResponse
    {
        $books = Auth::user()->books()
            ->with('category')
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $books]);
    }

    public function submitBook(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required_unless:is_free,1|numeric|min:0',
            'is_free'     => 'boolean',
            'language'    => 'required|in:fr,en,es,ar,pt,sw',
            'format'      => 'required|in:pdf,epub,both',
            'cover_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'book_file'   => 'required|mimes:pdf,epub|max:51200',
        ]);

        $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        $data['file_path']   = $request->file('book_file')->store('books/' . Auth::id(), 'local');
        $data['author_id']   = Auth::id();
        $data['status']      = 'pending';
        $data['is_free']     = $request->boolean('is_free');

        $book = Book::create($data);
        return response()->json(['success' => true, 'message' => 'Livre soumis pour validation.', 'data' => $book], 201);
    }

    public function earnings(): JsonResponse
    {
        $author    = Auth::user();
        $royalties = \App\Models\Royalty::where('author_id', $author->id)
            ->with('order.book:id,title')
            ->latest()->paginate(20);

        return response()->json(['success' => true, 'data' => [
            'royalties' => $royalties,
            'summary'   => [
                'total_earned'    => $author->total_earnings,
                'pending_balance' => $author->pending_balance,
            ],
        ]]);
    }
}
