<?php
namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, Royalty, WithdrawalRequest, BookTag};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, DB};
use Illuminate\Support\Str;

// ═══════════════════════════════════════════════════════════
// AUTHOR DASHBOARD  –  MySQL : DATE_FORMAT au lieu de TO_CHAR
// ═══════════════════════════════════════════════════════════
class DashboardController extends Controller
{
    public function index()
    {
        $author = Auth::user();

        $stats = [
            'total_books'     => $author->books()->count(),
            'published_books' => $author->books()->where('status', 'published')->count(),
            'pending_books'   => $author->books()->where('status', 'pending')->count(),
            'total_sales'     => Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))
                                      ->where('payment_status', 'paid')->count(),
            'total_revenue'   => $author->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance' => $author->royalties()->where('status', 'pending')->sum('net_amount'),
            'total_views'     => $author->books()->sum('views'),
            'total_downloads' => $author->books()->sum('downloads'),
            'followers_count' => $author->followers()->count(),
            'avg_rating'      => round($author->books()->avg('average_rating') ?? 0, 1),
        ];

        $recentSales = Order::whereHas('book', fn($q) => $q->where('author_id', $author->id))
            ->where('payment_status', 'paid')
            ->with(['book', 'user'])
            ->latest()->limit(8)->get();

        $topBooks = $author->books()
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->orderByDesc('orders_count')->limit(5)->get();

        // MySQL : DATE_FORMAT
        $monthlyRevenue = Royalty::where('author_id', $author->id)
            ->where('status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(net_amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)->get();

        return view('author.dashboard', compact('stats', 'recentSales', 'topBooks', 'monthlyRevenue'));
    }
}

// ═══════════════════════════════════════════════════════════
// AUTHOR BOOKS
// ═══════════════════════════════════════════════════════════
class BookController extends Controller
{
    public function index()
    {
        $books = Auth::user()->books()->with('category')
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->latest()->paginate(12);
        return view('author.books.index', compact('books'));
    }

    public function create()
    {
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        return view('author.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'description'      => 'required|string|min:100',
            'category_id'      => 'required|exists:categories,id',
            'price'            => 'required_unless:is_free,on|numeric|min:0',
            'is_free'          => 'boolean',
            'language'         => 'required|in:fr,en,es,ar,pt,sw',
            'pages'            => 'nullable|integer|min:1',
            'isbn'             => 'nullable|string|max:20',
            'publisher'        => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'format'           => 'required|in:pdf,epub,both',
            'print_on_demand'  => 'boolean',
            'print_price'      => 'nullable|numeric|min:0',
            'cover_image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'book_file'        => 'required|mimes:pdf,epub|max:512000',
            'tags'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, &$data) {
            $data['cover_image']    = $request->file('cover_image')->store('covers', 'public');
            $data['file_path']      = $request->file('book_file')->store('books/' . Auth::id(), 'local');
            $data['author_id']      = Auth::id();
            $data['is_free']        = $request->boolean('is_free');
            $data['print_on_demand']= $request->boolean('print_on_demand');
            $data['status']         = 'pending';

            $book = Book::create($data);

            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $request->tags));
                foreach (array_filter($tags) as $tag) {
                    BookTag::create(['book_id' => $book->id, 'tag' => strtolower($tag)]);
                }
            }
        });

        return redirect()->route('author.books.index')
            ->with('success', 'Livre soumis pour validation. Vous serez notifié sous 48h.');
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        $book->load('tags');
        return view('author.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        if (in_array($book->status, ['published', 'pending'])) {
            return back()->with('error', 'Un livre publié ou en attente ne peut être modifié que par le support.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required_unless:is_free,on|numeric|min:0',
            'is_free'     => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($data);
        return redirect()->route('author.books.index')->with('success', 'Livre mis à jour.');
    }

    public function stats(Book $book)
    {
        $this->authorize('view', $book);
        $book->load('reviews.user');

        // MySQL : DATE_FORMAT
        $salesByMonth = Order::where('book_id', $book->id)
            ->where('payment_status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as sales, SUM(amount) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)->get();

        $salesByMethod = Order::where('book_id', $book->id)
            ->where('payment_status', 'paid')
            ->selectRaw('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')->get();

        return view('author.books.stats', compact('book', 'salesByMonth', 'salesByMethod'));
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        if ($book->orders()->where('payment_status', 'paid')->exists()) {
            return back()->with('error', 'Impossible de supprimer un livre avec des ventes actives.');
        }
        Storage::disk('public')->delete($book->cover_image);
        Storage::disk('local')->delete($book->file_path);
        $book->delete();
        return redirect()->route('author.books.index')->with('success', 'Livre supprimé.');
    }
}

// ═══════════════════════════════════════════════════════════
// AUTHOR EARNINGS
// ═══════════════════════════════════════════════════════════
class EarningsController extends Controller
{
    public function index()
    {
        $author = Auth::user();

        $royalties = Royalty::where('author_id', $author->id)
            ->with('order.book')->latest()->paginate(15);

        $summary = [
            'total_earned'    => $author->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance' => $author->royalties()->where('status', 'pending')->sum('net_amount'),
            'total_withdrawn' => WithdrawalRequest::where('author_id', $author->id)
                                    ->where('status', 'completed')->sum('amount'),
        ];

        $withdrawals = WithdrawalRequest::where('author_id', $author->id)->latest()->paginate(10);

        return view('author.earnings.index', compact('royalties', 'summary', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request)
    {
        $author         = Auth::user();
        $pendingBalance = $author->royalties()->where('status', 'pending')->sum('net_amount');

        $data = $request->validate([
            'amount'         => "required|numeric|min:5000|max:{$pendingBalance}",
            'method'         => 'required|in:mtn_momo,airtel_money,bank',
            'account_number' => 'required|string|max:20',
            'account_name'   => 'required|string|max:100',
        ]);

        if (WithdrawalRequest::where('author_id', $author->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Vous avez déjà une demande de retrait en attente.');
        }

        WithdrawalRequest::create(array_merge($data, [
            'author_id'      => $author->id,
            'currency'       => 'XAF',
            'balance_before' => $pendingBalance,
        ]));

        return back()->with('success', 'Demande soumise. Traitement sous 48h.');
    }
}

// ═══════════════════════════════════════════════════════════
// AUTHOR PROFILE
// ═══════════════════════════════════════════════════════════
class ProfileController extends Controller
{
    public function edit()
    {
        return view('author.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'bio'     => 'nullable|string|max:1000',
            'phone'   => 'nullable|string|max:20',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:5',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:8']);
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        return back()->with('success', 'Profil mis à jour.');
    }
}
