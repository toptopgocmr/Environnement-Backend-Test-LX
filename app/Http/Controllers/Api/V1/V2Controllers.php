<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{
    ChatConversation, ChatMessage, PublicationPlan, AuthorPlan,
    AccountRequest, Book, Order, ShippingAddress, ReadingSession, Citation
};
use App\Services\{ChatService, AiReviewService, PhysicalStockService};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Illuminate\Support\Str;

// ═══════════════════════════════════════════════════════════
// CHAT API
// ═══════════════════════════════════════════════════════════
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    /** Liste des conversations de l'utilisateur connecté */
    public function index(): JsonResponse
    {
        $conversations = $this->chatService->getUserConversations(Auth::id());
        return response()->json(['success' => true, 'data' => $conversations]);
    }

    /** Ouvre ou récupère une conversation lecteur ↔ auteur depuis le panier */
    public function startWithAuthor(Request $request): JsonResponse
    {
        $request->validate([
            'book_id'  => 'required|exists:books,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $book = Book::with('author')->findOrFail($request->book_id);
        if (!$book->author) {
            return response()->json(['success' => false, 'message' => "Auteur introuvable."], 404);
        }

        $conversation = $this->chatService->getOrCreateReaderAuthorConversation(
            Auth::id(),
            $book->author_id,
            $book->id,
            $request->order_id
        );

        return response()->json([
            'success' => true,
            'data'    => $conversation->load(['messages.sender:id,name,avatar', 'participants.user:id,name,avatar,role', 'book:id,title,cover_image']),
        ]);
    }

    /** Récupère les messages d'une conversation */
    public function show(ChatConversation $conversation): JsonResponse
    {
        // Vérifier que l'user est participant
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $this->chatService->markAsRead($conversation, Auth::id());

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->with('sender:id,name,avatar')
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json(['success' => true, 'data' => $messages]);
    }

    /** Envoie un message */
    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'body' => 'required_without:file|nullable|string|max:2000',
            'file' => 'nullable|file|max:5120',
        ]);

        $filePath = $fileName = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files/' . $conversation->id, 'public');
            $fileName = $request->file('file')->getClientOriginalName();
        }

        $message = $this->chatService->sendMessage(
            $conversation,
            Auth::id(),
            $request->body ?? '',
            $request->hasFile('file') ? 'file' : 'text',
            $filePath,
            $fileName
        );

        return response()->json(['success' => true, 'data' => $message->load('sender:id,name,avatar')], 201);
    }

    /** Marque une conversation comme lue */
    public function markRead(ChatConversation $conversation): JsonResponse
    {
        $this->chatService->markAsRead($conversation, Auth::id());
        return response()->json(['success' => true]);
    }

    /** Nombre de messages non lus total */
    public function unreadCount(): JsonResponse
    {
        $count = ChatConversation::whereHas('participants', fn($q) => $q->where('user_id', Auth::id()))
            ->withCount(['messages as unread' => fn($q) => $q->where('sender_id', '!=', Auth::id())->where('is_read', false)])
            ->get()
            ->sum('unread');

        return response()->json(['success' => true, 'data' => ['unread_count' => $count]]);
    }
}

// ═══════════════════════════════════════════════════════════
// PUBLICATION PLANS API
// ═══════════════════════════════════════════════════════════
class PublicationPlanController extends Controller
{
    /** Liste des formules disponibles */
    public function index(): JsonResponse
    {
        $plans = PublicationPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    /** Souscrire à une formule */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id'        => 'required|exists:publication_plans,id',
            'billing'        => 'required|in:monthly,annual',
            'payment_method' => 'required|in:mtn_momo,airtel_money,stripe,free',
            'phone'          => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string',
        ]);

        $plan = PublicationPlan::findOrFail($data['plan_id']);
        $user = Auth::user();
        $price = $data['billing'] === 'annual' ? $plan->price_annual : $plan->price_monthly;

        // Vérifier si déjà abonné à un plan actif
        $existing = AuthorPlan::where('user_id', $user->id)->where('status', 'active')->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Vous avez déjà un plan actif.'], 400);
        }

        $authorPlan = AuthorPlan::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'billing'        => $data['billing'],
            'status'         => $price == 0 ? 'active' : 'pending_payment',
            'amount_paid'    => $price,
            'currency'       => $plan->currency,
            'payment_method' => $data['payment_method'],
            'starts_at'      => $price == 0 ? now() : null,
            'ends_at'        => $price == 0 ? now()->addMonth() : null,
        ]);

        if ($price == 0) {
            // Plan gratuit → activer le rôle auteur immédiatement
            $user->update(['role' => 'author']);
        }

        return response()->json(['success' => true, 'data' => $authorPlan->load('plan')], 201);
    }

    /** Plan actif de l'utilisateur connecté */
    public function myPlan(): JsonResponse
    {
        $plan = AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('plan')
            ->first();
        return response()->json(['success' => true, 'data' => $plan]);
    }
}

// ═══════════════════════════════════════════════════════════
// ACCOUNT REQUEST (demande d'activation)
// ═══════════════════════════════════════════════════════════
class AccountRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'                 => 'required|in:author,auditor,institution',
            'motivation'           => 'required|string|min:50|max:1000',
            'document'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'institution_name'    => 'required_if:type,institution|nullable|string',
            'institution_country' => 'required_if:type,institution|nullable|string',
        ]);

        $existing = AccountRequest::where('user_id', Auth::id())
            ->whereIn('status', ['pending'])
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Demande déjà en cours.'], 400);
        }

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('account_documents', 'local');
        }

        $req = AccountRequest::create(array_merge($data, ['user_id' => Auth::id()]));
        return response()->json(['success' => true, 'data' => $req, 'message' => 'Demande soumise. Traitement sous 48h.'], 201);
    }

    public function myRequest(): JsonResponse
    {
        $req = AccountRequest::where('user_id', Auth::id())->latest()->first();
        return response()->json(['success' => true, 'data' => $req]);
    }
}

// ═══════════════════════════════════════════════════════════
// COMMANDES PHYSIQUES
// ═══════════════════════════════════════════════════════════
class PhysicalOrderController extends Controller
{
    public function __construct(private readonly PhysicalStockService $stockService) {}

    public function initiate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id'            => 'required|exists:books,id',
            'payment_method'     => 'required|in:mtn_momo,airtel_money,stripe',
            'phone'              => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string',
            'shipping_address_id'=> 'nullable|exists:shipping_addresses,id',
            // ou nouvelle adresse
            'full_name'         => 'required_without:shipping_address_id|nullable|string',
            'phone_shipping'    => 'required_without:shipping_address_id|nullable|string',
            'address_line1'     => 'required_without:shipping_address_id|nullable|string',
            'city'              => 'required_without:shipping_address_id|nullable|string',
            'country'           => 'nullable|string|size:2',
        ]);

        $book = Book::findOrFail($data['book_id']);
        $user = Auth::user();

        if (!$this->stockService->isAvailable($book->id)) {
            return response()->json(['success' => false, 'message' => 'Stock épuisé pour ce livre.'], 400);
        }

        // Créer ou récupérer l'adresse
        $addressId = $data['shipping_address_id'] ?? null;
        if (!$addressId) {
            $address = ShippingAddress::create([
                'user_id'      => $user->id,
                'full_name'    => $data['full_name'],
                'phone'        => $data['phone_shipping'],
                'address_line1'=> $data['address_line1'],
                'city'         => $data['city'],
                'country'      => $data['country'] ?? 'CG',
            ]);
            $addressId = $address->id;
        }

        // Réserver le stock
        $reserved = $this->stockService->reserveStock($book->id);
        if (!$reserved) {
            return response()->json(['success' => false, 'message' => 'Stock insuffisant.'], 400);
        }

        $price = $book->physical_price ?? $book->print_price ?? $book->price;

        $order = Order::create([
            'reference'           => 'PHY' . strtoupper(Str::random(7)),
            'user_id'             => $user->id,
            'book_id'             => $book->id,
            'amount'              => $price,
            'currency'            => $book->currency,
            'type'                => 'print',
            'payment_method'      => $data['payment_method'],
            'payment_status'      => 'pending',
            'shipping_address_id' => $addressId,
            'shipping_status'     => 'none',
            'max_downloads'       => 0,
        ]);

        return response()->json(['success' => true, 'data' => $order->load('book:id,title,cover_image')], 201);
    }

    public function myPhysicalOrders(): JsonResponse
    {
        $orders = Order::where('user_id', Auth::id())
            ->where('type', 'print')
            ->with(['book:id,title,cover_image', 'shippingAddress'])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function shippingAddresses(): JsonResponse
    {
        $addresses = ShippingAddress::where('user_id', Auth::id())->get();
        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'    => 'required|string',
            'phone'        => 'required|string',
            'address_line1'=> 'required|string',
            'address_line2'=> 'nullable|string',
            'city'         => 'required|string',
            'state'        => 'nullable|string',
            'postal_code'  => 'nullable|string',
            'country'      => 'nullable|string|size:2',
            'is_default'   => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            ShippingAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address = ShippingAddress::create(array_merge($data, ['user_id' => Auth::id()]));
        return response()->json(['success' => true, 'data' => $address], 201);
    }
}

// ═══════════════════════════════════════════════════════════
// CITATIONS
// ═══════════════════════════════════════════════════════════
class CitationController extends Controller
{
    public function __construct(private readonly AiReviewService $aiService) {}

    public function generate(Request $request, Book $book): JsonResponse
    {
        $request->validate(['style' => 'required|in:apa,mla,chicago,ieee,harvard']);

        $book->load('author:id,name');
        $text = $this->aiService->generateCitation($book, $request->style);

        // Sauvegarder pour l'historique
        if (Auth::check()) {
            Citation::updateOrCreate(
                ['book_id' => $book->id, 'user_id' => Auth::id(), 'style' => $request->style],
                ['citation_text' => $text]
            );
        }

        return response()->json(['success' => true, 'data' => ['citation' => $text, 'style' => $request->style]]);
    }

    public function myCitations(): JsonResponse
    {
        $citations = Citation::where('user_id', Auth::id())
            ->with('book:id,title,cover_image')
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $citations]);
    }
}

// ═══════════════════════════════════════════════════════════
// SESSIONS DE LECTURE (location)
// ═══════════════════════════════════════════════════════════
class ReadingSessionController extends Controller
{
    public function rent(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'duration_hours' => 'required|integer|in:6,24,72',
            'payment_method' => 'required|in:mtn_momo,airtel_money,stripe',
            'phone'          => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string',
        ]);

        if (!$book->allow_rental) {
            return response()->json(['success' => false, 'message' => 'Ce livre ne propose pas la location.'], 400);
        }

        $user  = Auth::user();
        $hours = $request->duration_hours;
        $price = ($book->rental_price_hour ?? 0) * $hours;

        // Vérifier session active existante
        $existing = ReadingSession::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'data' => $existing, 'message' => 'Session déjà active.']);
        }

        $session = ReadingSession::create([
            'user_id'        => $user->id,
            'book_id'        => $book->id,
            'token'          => Str::random(64),
            'status'         => $price == 0 ? 'active' : 'active', // à conditionner au paiement en prod
            'amount_paid'    => $price,
            'currency'       => $book->currency,
            'duration_hours' => $hours,
            'starts_at'      => now(),
            'expires_at'     => now()->addHours($hours),
        ]);

        return response()->json(['success' => true, 'data' => $session], 201);
    }

    public function myActiveSessions(): JsonResponse
    {
        $sessions = ReadingSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('book:id,title,cover_image,author_id')
            ->get();
        return response()->json(['success' => true, 'data' => $sessions]);
    }
}
