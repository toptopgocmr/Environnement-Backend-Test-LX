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
