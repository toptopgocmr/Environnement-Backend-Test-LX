<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

// ═══════════════════════════════════════════════════════════
// ADMIN DASHBOARD
// ═══════════════════════════════════════════════════════════
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'         => User::where('role', 'reader')->count(),
            'total_authors'       => User::where('role', 'author')->count(),
            'total_books'         => Book::published()->count(),
            'pending_books'       => Book::pending()->count(),
            'total_orders'        => Order::where('payment_status', 'paid')->count(),
            'total_revenue'       => Order::where('payment_status', 'paid')->sum('amount'),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'new_users_today'     => User::whereDate('created_at', today())->count(),
        ];

        $recentOrders = Order::with(['user', 'book'])
            ->where('payment_status', 'paid')
            ->latest()->limit(10)->get();

        $topBooks = Book::published()
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->orderByDesc('orders_count')
            ->limit(5)->get();

        // MySQL : DATE_FORMAT au lieu de TO_CHAR
        $revenueChart = Order::where('payment_status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)->get();

        $pendingBooks       = Book::pending()->with('author')->latest()->limit(5)->get();
        $pendingWithdrawals = WithdrawalRequest::where('status', 'pending')->with('author')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'topBooks', 'revenueChart', 'pendingBooks', 'pendingWithdrawals'
        ));
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN BOOKS
// ═══════════════════════════════════════════════════════════
class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with(['author', 'category'])
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->search,      fn($q) => $q->where('title', 'LIKE', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->latest()->paginate(20);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.books.index', compact('books', 'categories'));
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category', 'reviews.user', 'tags']);
        $orderStats = [
            'total'   => $book->orders()->where('payment_status', 'paid')->count(),
            'revenue' => $book->orders()->where('payment_status', 'paid')->sum('amount'),
        ];
        return view('admin.books.show', compact('book', 'orderStats'));
    }

    public function approve(Book $book)
    {
        $book->update(['status' => 'published']);
        $book->author->notify(new \App\Notifications\BookApproved($book));
        return back()->with('success', "Livre « {$book->title} » publié.");
    }

    public function reject(Request $request, Book $book)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $book->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        $book->author->notify(new \App\Notifications\BookRejected($book, $request->reason));
        return back()->with('success', "Livre rejeté. L'auteur a été notifié.");
    }

    public function toggleFeatured(Book $book)
    {
        $book->update(['is_featured' => !$book->is_featured]);
        return back()->with('success', 'Statut "mis en avant" modifié.');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image)  Storage::disk('public')->delete($book->cover_image);
        if ($book->file_path)    Storage::disk('local')->delete($book->file_path);
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Livre supprimé.');
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN USERS
// ═══════════════════════════════════════════════════════════
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',  'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            }))
            ->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('books', 'orders.book');
        $stats = [
            'books_count'    => $user->books()->count(),
            'orders_count'   => $user->orders()->where('payment_status', 'paid')->count(),
            'total_earnings' => $user->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance'=> $user->royalties()->where('status', 'pending')->sum('net_amount'),
        ];
        return view('admin.users.show', compact('user', 'stats'));
    }

    public function toggleActive(User $user)
    {
        if ($user->isAdmin()) return back()->with('error', 'Impossible de suspendre un administrateur.');
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function verifyAuthor(User $user)
    {
        $user->update(['is_verified_author' => true, 'role' => 'author']);
        return back()->with('success', "Auteur {$user->name} vérifié.");
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) return back()->with('error', 'Impossible de supprimer un administrateur.');
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN WITHDRAWALS
// ═══════════════════════════════════════════════════════════
class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = WithdrawalRequest::with('author')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(WithdrawalRequest $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update(['status' => 'processing']);
            // En production : déclencher paiement MoMo/Airtel ici
            $withdrawal->update(['status' => 'completed']);
            $withdrawal->author->royalties()
                ->where('status', 'pending')
                ->update(['status' => 'paid', 'paid_at' => now()]);
        });
        return back()->with('success', 'Retrait approuvé et traité.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate(['reason' => 'required|string']);
        $withdrawal->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        return back()->with('success', 'Demande rejetée.');
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN ORDERS
// ═══════════════════════════════════════════════════════════
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'book.author'])
            ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
            ->when($request->method, fn($q) => $q->where('payment_method', $request->method))
            ->latest()->paginate(20);

        $summary = [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('amount'),
            'today_revenue' => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('amount'),
            'pending_count' => Order::where('payment_status', 'pending')->count(),
        ];
        return view('admin.orders.index', compact('orders', 'summary'));
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN CATEGORIES
// ═══════════════════════════════════════════════════════════
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->orderBy('sort_order')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:10',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);
        $data['slug'] = \Str::slug($data['name']);
        Category::create($data);
        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'description' => 'nullable|string',
            'color'     => 'nullable|string|max:10',
            'icon'      => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);
        $category->update($data);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) return back()->with('error', 'Cette catégorie contient des livres.');
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN SETTINGS
// ═══════════════════════════════════════════════════════════
class SettingsController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'platform_fee_percent'    => 'required|numeric|min:0|max:50',
            'min_withdrawal_amount'   => 'required|numeric|min:1000',
            'max_downloads_per_order' => 'required|integer|min:1|max:20',
            'maintenance_mode'        => 'boolean',
            'platform_name'           => 'required|string|max:100',
            'support_email'           => 'required|email',
            'support_phone'           => 'nullable|string|max:20',
        ]);
        foreach ($data as $key => $value) {
            PlatformSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return back()->with('success', 'Paramètres mis à jour.');
    }
}
