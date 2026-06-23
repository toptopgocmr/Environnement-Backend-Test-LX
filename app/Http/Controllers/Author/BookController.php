<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, Royalty, WithdrawalRequest, BookTag, AuthorPlan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, DB};
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index()
    {
        $books = Auth::user()->books()->with('category')
            ->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])
            ->latest()->paginate(12);
        return view('author.books.index', compact('books'));
    }

    /* ── Vérifie le forfait actif et retourne [activePlan, plan] ou redirige ── */
    private function getActivePlanOrFail()
    {
        $activePlan = AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        if (!$activePlan || !$activePlan->isActive()) {
            return null;
        }

        return $activePlan;
    }

    public function create()
    {
        $activePlan = $this->getActivePlanOrFail();

        if (!$activePlan) {
            return redirect()->route('author.plans.index')
                ->with('error', 'Vous devez souscrire à un forfait pour publier un livre.');
        }

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        return view('author.books.create', compact('categories', 'activePlan'));
    }

    public function store(Request $request)
    {
        $academicTypes = ['these','memoire_master','memoire_licence','rapport_stage','rapport','article','manuel','cours'];

        // ── Vérification forfait actif (sécurité serveur) ──────────────────────
        $activePlan = $this->getActivePlanOrFail();
        if (!$activePlan) {
            return redirect()->route('author.plans.index')
                ->with('error', 'Votre forfait est expiré ou inexistant. Veuillez vous abonner pour publier.');
        }

        $plan = $activePlan->plan;

        // ── Limite du nombre de livres du forfait ───────────────────────────────
        if ($plan->max_books !== null) {
            $publishedCount = Book::where('author_id', Auth::id())
                ->whereNotIn('status', ['rejected', 'draft'])
                ->count();
            if ($publishedCount >= $plan->max_books) {
                return back()->withInput()
                    ->with('error', "Votre forfait « {$plan->name} » est limité à {$plan->max_books} livre(s). Passez à un forfait supérieur pour en publier davantage.");
            }
        }

        // ── Vérification : types académiques nécessitent allow_academic ─────────
        if (in_array($request->input('document_type'), $academicTypes) && !$plan->allow_academic) {
            return back()->withInput()
                ->with('error', "Votre forfait « {$plan->name} » ne permet pas la publication de documents académiques. Veuillez passer à un forfait supérieur.");
        }

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'description'         => 'required|string|min:100|max:3000',
            'document_type'       => 'required|string|max:50',
            'category_id'         => 'required|exists:categories,id',
            'language'            => 'required|in:fr,en,ln,kg,sw,es,pt,ar',
            'price'               => 'required_unless:is_free,on|numeric|min:0',
            'currency'            => 'nullable|in:XAF,USD,EUR',
            'is_free'             => 'boolean',
            'pages'               => 'nullable|integer|min:1',
            'isbn'                => 'nullable|string|max:20',
            'publisher'           => 'nullable|string|max:100',
            'publication_year'    => 'nullable|integer|min:1800|max:' . date('Y'),
            'format'              => 'required|in:pdf,epub,both',
            'rights'              => 'nullable|string|max:30',
            'print_on_demand'     => 'boolean',
            'print_price'         => 'nullable|numeric|min:0',
            'allow_rental'        => 'boolean',
            'rental_price_hour'   => 'nullable|numeric|min:0',
            // Académique (conditionnel)
            'university'          => 'nullable|string|max:200',
            'supervisor'          => 'nullable|string|max:100',
            'field_of_study'      => 'nullable|string|max:100',
            // Fichiers
            'cover_image'         => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'book_file'           => 'required|mimes:pdf,epub|max:512000',
            'preview_file'        => 'nullable|mimes:pdf,epub|max:20480',
            'tags'                => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, &$data, $academicTypes) {
            $data['cover_image']     = $request->file('cover_image')->store('covers', 'public');
            $data['file_path']       = $request->file('book_file')->store('books/' . Auth::id(), 'local');
            $data['author_id']       = Auth::id();
            $data['is_free']         = $request->boolean('is_free');
            $data['print_on_demand'] = $request->boolean('print_on_demand');
            $data['allow_rental']    = $request->boolean('allow_rental');
            $data['currency']        = $request->input('currency', 'XAF');
            $data['status']          = 'pending';

            if ($request->hasFile('preview_file')) {
                $data['preview_path'] = $request->file('preview_file')->store('previews/' . Auth::id(), 'local');
            }

            // Effacer les champs académiques si le type ne l'est pas
            if (!in_array($data['document_type'], $academicTypes)) {
                $data['university']    = null;
                $data['supervisor']    = null;
                $data['field_of_study']= null;
            }

            $book = Book::create($data);

            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $request->tags));
                foreach (array_filter($tags) as $tag) {
                    BookTag::create(['book_id' => $book->id, 'tag' => strtolower($tag)]);
                }
            }
        });

        return redirect()->route('author.books.index')
            ->with('success', 'Livre soumis pour validation. Vous serez notifié sous 48 heures ouvrées.');
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
