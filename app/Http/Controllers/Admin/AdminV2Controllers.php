<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AiReview, AccountRequest, Book, Order, User, ChatConversation, PhysicalStockMovement};
use App\Services\{AiReviewService, ChatService, PhysicalStockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ═══════════════════════════════════════════════════════════
// ADMIN — ANALYSES IA
// ═══════════════════════════════════════════════════════════
class AiReviewController extends Controller
{
    public function __construct(private readonly AiReviewService $aiService) {}

    public function index(Request $request)
    {
        $reviews = AiReview::with(['book.author'])
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->recommendation,fn($q) => $q->where('recommendation', $request->recommendation))
            ->latest()->paginate(20);
        return view('admin.ai_reviews.index', compact('reviews'));
    }

    public function show(AiReview $aiReview)
    {
        $aiReview->load(['book.author', 'book.category']);
        return view('admin.ai_reviews.show', compact('aiReview'));
    }

    /** Lance une nouvelle analyse IA pour un livre */
    public function analyze(Book $book)
    {
        dispatch(function () use ($book) {
            app(AiReviewService::class)->analyze($book);
        })->afterResponse();

        return back()->with('success', "Analyse IA lancée pour « {$book->title} ».");
    }

    /** L'admin approuve un livre après lecture du rapport IA */
    public function approveWithAi(Request $request, Book $book)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($book, $request) {
            $book->update(['status' => 'published']);
            if ($book->aiReview) {
                $book->aiReview->update(['admin_decision_note' => $request->note]);
            }
            $book->author->notify(new \App\Notifications\BookApproved($book));
        });

        return back()->with('success', "Livre « {$book->title} » approuvé et publié.");
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN — DEMANDES D'ACTIVATION
// ═══════════════════════════════════════════════════════════
class AccountRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $requests = AccountRequest::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->latest()->paginate(20);

        $counts = [
            'pending'  => AccountRequest::where('status', 'pending')->count(),
            'approved' => AccountRequest::where('status', 'approved')->count(),
            'rejected' => AccountRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.accounts.index', compact('requests', 'counts'));
    }

    public function show(AccountRequest $accountRequest)
    {
        $accountRequest->load('user');
        return view('admin.accounts.show', compact('accountRequest'));
    }

    public function approve(Request $request, AccountRequest $accountRequest)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($accountRequest, $request) {
            $accountRequest->update([
                'status'       => 'approved',
                'admin_note'   => $request->note,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
            ]);

            // Activer le compte + attribuer le bon rôle
            $user = $accountRequest->user;
            $user->update([
                'is_active'          => true,
                'role'               => $accountRequest->type === 'institution' ? 'author' : $accountRequest->type,
                'is_verified_author' => $accountRequest->type === 'author',
            ]);

            // Notifier
            $user->notify(new \App\Notifications\AccountApproved($accountRequest->type));
        });

        return back()->with('success', 'Compte activé avec succès.');
    }

    public function reject(Request $request, AccountRequest $accountRequest)
    {
        $request->validate(['note' => 'required|string|max:500']);

        $accountRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $accountRequest->user->notify(new \App\Notifications\AccountRejected($request->note));

        return back()->with('success', 'Demande rejetée.');
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN — COMMANDES PHYSIQUES
// ═══════════════════════════════════════════════════════════
class PhysicalOrderAdminController extends Controller
{
    public function __construct(private readonly PhysicalStockService $stockService) {}

    public function index(Request $request)
    {
        $orders = Order::where('type', 'print')
            ->with(['user', 'book', 'shippingAddress'])
            ->when($request->status, fn($q) => $q->where('shipping_status', $request->status))
            ->latest()->paginate(20);

        $summary = [
            'pending'    => Order::where('type','print')->where('shipping_status','none')->where('payment_status','paid')->count(),
            'processing' => Order::where('type','print')->where('shipping_status','processing')->count(),
            'shipped'    => Order::where('type','print')->where('shipping_status','shipped')->count(),
            'delivered'  => Order::where('type','print')->where('shipping_status','delivered')->count(),
        ];

        return view('admin.physical.orders', compact('orders', 'summary'));
    }

    public function updateShipping(Request $request, Order $order)
    {
        $request->validate([
            'shipping_status'  => 'required|in:processing,shipped,delivered',
            'tracking_number'  => 'nullable|string',
            'carrier'          => 'nullable|string',
            'shipping_note'    => 'nullable|string',
        ]);

        $update = $request->only(['shipping_status','tracking_number','carrier','shipping_note']);
        if ($request->shipping_status === 'shipped') $update['shipped_at'] = now();
        if ($request->shipping_status === 'delivered') $update['delivered_at'] = now();

        $order->update($update);
        $order->user->notify(new \App\Notifications\ShippingUpdated($order));

        return back()->with('success', 'Statut de livraison mis à jour.');
    }

    public function stock(Request $request)
    {
        $books = Book::where('print_on_demand', true)
            ->orWhere('physical_price', '>', 0)
            ->with('author:id,name')
            ->withCount(['physicalOrders as sold' => fn($q) => $q->where('payment_status', 'paid')])
            ->paginate(20);

        return view('admin.physical.stock', compact('books'));
    }

    public function addStock(Request $request, Book $book)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string',
        ]);
        $this->stockService->addStock($book->id, $request->quantity, $request->reason ?? 'Ajout manuel', auth()->id());
        return back()->with('success', "Stock mis à jour pour « {$book->title} ».");
    }
}

// ═══════════════════════════════════════════════════════════
// ADMIN — CHAT
// ═══════════════════════════════════════════════════════════
class AdminChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    public function index(Request $request)
    {
        $conversations = ChatConversation::with([
            'participants.user:id,name,avatar,role',
            'lastMessage.sender:id,name',
            'book:id,title',
        ])
        ->when($request->type,   fn($q) => $q->where('type', $request->type))
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->orderByDesc('last_message_at')
        ->paginate(20);

        return view('admin.chat.index', compact('conversations'));
    }

    public function show(ChatConversation $conversation)
    {
        $conversation->load(['participants.user:id,name,avatar,role', 'book:id,title,cover_image']);
        $messages = $conversation->messages()->with('sender:id,name,avatar')->paginate(100);
        $this->chatService->markAsRead($conversation, auth()->id());
        return view('admin.chat.show', compact('conversation', 'messages'));
    }

    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate(['body' => 'required|string|max:2000']);
        $this->chatService->sendMessage($conversation, auth()->id(), $request->body);
        return back()->with('success', 'Message envoyé.');
    }

    /** Ouvre une conversation admin → auteur */
    public function startWithAuthor(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'subject' => 'nullable|string']);
        $conv = $this->chatService->getOrCreateAdminConversation(
            auth()->id(), $request->user_id, 'admin_author', $request->subject
        );
        return redirect()->route('admin.chat.show', $conv);
    }

    /** Ouvre une conversation admin → lecteur */
    public function startWithReader(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'subject' => 'nullable|string']);
        $conv = $this->chatService->getOrCreateAdminConversation(
            auth()->id(), $request->user_id, 'admin_reader', $request->subject
        );
        return redirect()->route('admin.chat.show', $conv);
    }

    public function close(ChatConversation $conversation)
    {
        $conversation->update(['status' => 'closed']);
        return back()->with('success', 'Conversation fermée.');
    }
}
