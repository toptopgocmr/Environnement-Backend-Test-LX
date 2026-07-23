<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Category, Review, Royalty, ReadingProgress};
use App\Services\PaymentService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Storage, Http};
use Tymon\JWTAuth\Facades\JWTAuth;

// BOOKS API  –  MySQL : LIKE au lieu de ILIKE
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
            ->when($request->has('is_free'), fn($q) => $q->where('is_free', (bool)(int)$request->is_free))
            ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->sort === 'popular', fn($q) => $q->orderByDesc('downloads'))
            ->when($request->sort === 'rated',   fn($q) => $q->orderByDesc('average_rating'))
            ->when(in_array($request->sort, ['newest', 'recent']) || !$request->sort, fn($q) => $q->latest())
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

    public function getReviews(Book $book): JsonResponse
    {
        if ($book->status !== 'published') abort(404);
        $reviews = $book->reviews()
            ->where('is_approved', true)
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    /**
     * Téléchargement du fichier brut désactivé volontairement : un livre numérique
     * acheté (ou gratuit) se lit exclusivement via le lecteur intégré (streaming
     * par token temporaire, cf. readLink()/streamBook()) et reste stocké dans
     * l'espace lecteur de l'utilisateur — il n'est jamais téléchargeable.
     */
    public function download(Book $book): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "Le téléchargement n'est pas autorisé. Lisez ce livre depuis votre espace lecteur.",
        ], 403);
    }

    /** Nombre de pages consultables gratuitement avant paywall pour un livre payant non acquis. */
    public const FREE_PREVIEW_PAGES = 5;

    /** Génère un token de lecture temporaire (1h) et renvoie l'URL de streaming.
     *  - Livre gratuit                       : accessible sans authentification, lecture complète
     *  - Livre payant, acheté/abonnement actif : lecture complète
     *  - Livre payant, non acquis              : aperçu limité à FREE_PREVIEW_PAGES pages, puis paywall
     */
    public function readLink(Book $book): JsonResponse
    {
        $isPreview    = false;
        $previewPages = null;

        // Vérification des droits
        if (!$book->is_free) {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Connexion requise.'], 401);
            }
            if (!$user->hasPurchased($book->id) && !$user->hasActiveSubscription()) {
                $isPreview    = true;
                $previewPages = min(self::FREE_PREVIEW_PAGES, $book->pages ?: self::FREE_PREVIEW_PAGES);
            }
        }

        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            return response()->json(['success' => false, 'message' => 'Fichier non disponible.'], 404);
        }

        $token = \Str::random(64);
        \Cache::put('read_token_' . $token, $book->id, now()->addHour());

        $url = url("/api/v1/books/{$book->id}/stream/{$token}");
        return response()->json(['success' => true, 'data' => [
            'url'            => $url,
            'format'         => $book->format ?: 'pdf',
            'pages'          => $book->pages,
            'is_preview'     => $isPreview,
            'preview_pages'  => $previewPages,
        ]]);
    }

    /** Streaming PDF via token temporaire — accessible depuis un iframe sans Bearer header */
    public function streamBook(Book $book, string $token): mixed
    {
        $cachedId = \Cache::get('read_token_' . $token);
        if (!$cachedId || (int) $cachedId !== $book->id) {
            abort(403, 'Token invalide ou expiré.');
        }
        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            abort(404, 'Fichier introuvable.');
        }
        $path = Storage::disk('local')->path($book->file_path);
        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \Str::slug($book->title) . '.pdf"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
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

    /** Assistant IA — répond aux questions sur le livre via Claude */
    /**
     * Assistant IA — Google Gemini avec cache et rate-limiting par utilisateur.
     *
     * Stratégie multi-utilisateurs :
     *  1. Cache 24h par (livre + question) — 1 appel API pour N utilisateurs posant la même question
     *  2. Rate limit : max 10 questions / utilisateur / heure (IP pour non-connectés)
     *  3. Retry automatique sur 429 (quota Gemini) avec backoff 2s
     */
    public function ask(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'question'   => 'required|string|max:1000',
            'page'       => 'nullable|integer',
            'page_text'  => 'nullable|string|max:5000', // texte extrait de la page (optionnel)
        ]);

        $apiKey = env('GEMINI_API_KEY', '');
        if (!$apiKey || in_array(trim($apiKey), ['', 'YOUR_GEMINI_KEY', 'AIza...'])) {
            return response()->json(['success' => false,
                'message' => 'GEMINI_API_KEY non configuré.'], 503);
        }

        // ── 1. Rate-limiting par utilisateur (10 req/h) ──────────────────────
        $userId   = Auth::id() ?? $request->ip();
        $rateKey  = "ai_rate_{$userId}";
        $reqCount = (int) \Cache::get($rateKey, 0);
        $rateLimit = (int) env('AI_RATE_LIMIT_PER_HOUR', 10);
        if ($reqCount >= $rateLimit) {
            return response()->json(['success' => false,
                'message' => "Limite atteinte : {$rateLimit} questions/heure par utilisateur. Réessayez plus tard."], 429);
        }
        \Cache::put($rateKey, $reqCount + 1, now()->addHour());

        // ── 2. Cache de réponse (24h par livre + question + page) ────────────
        $cacheKey = 'ai_ans_' . md5($book->id . '|' . strtolower(trim($data['question'])) . '|' . ($data['page'] ?? ''));
        if ($cached = \Cache::get($cacheKey)) {
            return response()->json(['success' => true, 'data' => ['answer' => $cached, 'cached' => true]]);
        }

        // ── 3. Extraire le texte PDF de la page si disponible ────────────────
        $pageText = $data['page_text'] ?? null;
        if (!$pageText && $data['page'] && $book->file_path && \Storage::disk('local')->exists($book->file_path)) {
            try {
                $pdfPath = \Storage::disk('local')->path($book->file_path);
                if (class_exists('\Smalot\PdfParser\Parser')) {
                    $parser   = new \Smalot\PdfParser\Parser();
                    $pdf      = $parser->parseFile($pdfPath);
                    $pages    = $pdf->getPages();
                    $pageIdx  = ($data['page'] - 1);
                    if (isset($pages[$pageIdx])) {
                        $pageText = trim($pages[$pageIdx]->getText());
                        if (strlen($pageText) > 3000) $pageText = substr($pageText, 0, 3000) . '...';
                    }
                }
            } catch (\Exception $e) { $pageText = null; }
        }

        // ── 4. Contexte et prompt ─────────────────────────────────────────────
        $context = "Titre : {$book->title}\n"
            . "Auteur : {$book->author?->name}\n"
            . "Éditeur : " . ($book->publisher ?? 'N/A') . "\n"
            . "Année : " . ($book->publication_year ?? 'N/A') . "\n"
            . "Description : {$book->description}\n"
            . ($data['page'] ? "Page {$data['page']}\n" : '');

        // Si on a le texte réel de la page, on l'inclut dans le prompt
        if ($pageText) {
            $context .= "\n--- Contenu de la page {$data['page']} ---\n{$pageText}\n---";
        }

        $prompt = "Tu es un assistant littéraire intelligent intégré à la plateforme LireX. "
            . "Tu aides les lecteurs à comprendre et traduire les œuvres. "
            . "Tu réponds de façon claire et concise (max 300 mots). "
            . "Si on te demande de traduire, traduis le contenu fourni fidèlement.\n\n"
            . "Contexte du livre :\n{$context}\n\nQuestion : {$data['question']}";

        // ── 4. Appel Gemini avec retry sur 429 ───────────────────────────────
        $model   = env('GEMINI_MODEL', 'gemini-2.0-flash');
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $payload = [
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['maxOutputTokens' => 600, 'temperature' => 0.7],
        ];

        $maxAttempts = 2;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post($url, $payload);

                if ($response->successful()) {
                    $answer = $response->json('candidates.0.content.parts.0.text') ?? 'Réponse indisponible.';
                    // Mise en cache 24h
                    \Cache::put($cacheKey, $answer, now()->addHours(24));
                    return response()->json(['success' => true, 'data' => ['answer' => $answer]]);
                }

                if ($response->status() === 429 && $attempt < $maxAttempts) {
                    sleep(2); // Backoff 2s avant retry
                    continue;
                }

                $errDetail = $response->json('error.message') ?? $response->status();
                return response()->json(['success' => false,
                    'message' => "Erreur Gemini ({$response->status()}) : {$errDetail}"], $response->status() === 429 ? 429 : 500);

            } catch (\Exception $e) {
                if ($attempt < $maxAttempts) { sleep(1); continue; }
                return response()->json(['success' => false, 'message' => 'Erreur réseau : ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Service temporairement indisponible.'], 503);
    }
}
