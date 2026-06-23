<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{ChatConversation, ChatParticipant, User};
use App\Services\ChatService;
use Illuminate\Http\Request;

class AuthorChatController extends Controller
{
    public function __construct(private readonly ChatService $chatService) {}

    /** Liste toutes les conversations de l'auteur */
    public function index(Request $request)
    {
        $author = auth()->user();
        $type   = $request->get('type', 'all'); // all | admin | clients

        $query = ChatConversation::whereHas('participants', fn($q) => $q->where('user_id', $author->id))
            ->with([
                'participants.user:id,name,avatar,role',
                'lastMessage.sender:id,name',
                'book:id,title,cover_image',
            ])
            ->withCount(['messages as unread_count' => fn($q) =>
                $q->where('sender_id', '!=', $author->id)->where('is_read', false)
            ])
            ->orderByDesc('last_message_at');

        if ($type === 'admin') {
            $query->where('type', 'admin_author');
        } elseif ($type === 'clients') {
            $query->where('type', 'reader_author');
        }

        $conversations = $query->paginate(20);
        $totalUnread   = $query->getQuery()->get()->sum('unread_count');

        return view('author.chat.index', compact('conversations', 'type', 'totalUnread'));
    }

    /** Affiche une conversation */
    public function show(ChatConversation $conversation)
    {
        $author = auth()->user();

        // Vérifier que l'auteur est bien participant
        abort_unless(
            $conversation->participants()->where('user_id', $author->id)->exists(),
            403
        );

        $conversation->load(['participants.user:id,name,avatar,role', 'book:id,title,cover_image']);
        $messages = $conversation->messages()->with('sender:id,name,avatar')->paginate(100);

        $this->chatService->markAsRead($conversation, $author->id);

        return view('author.chat.show', compact('conversation', 'messages'));
    }

    /** Envoie un message */
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $author = auth()->user();

        abort_unless(
            $conversation->participants()->where('user_id', $author->id)->exists(),
            403
        );

        $request->validate(['body' => 'required|string|max:2000']);

        $this->chatService->sendMessage($conversation, $author->id, $request->body);

        return back()->with('success', 'Message envoyé.');
    }

    /** Ouvre/retrouve une conversation auteur → admin */
    public function startWithAdmin(Request $request)
    {
        $author = auth()->user();
        $admin  = User::where('role', 'admin')->first();

        abort_unless($admin, 404, 'Aucun administrateur disponible.');

        $request->validate(['subject' => 'nullable|string|max:255']);

        $conv = $this->chatService->getOrCreateAdminConversation(
            $admin->id,
            $author->id,
            'admin_author',
            $request->subject ?? 'Question à l\'administration'
        );

        return redirect()->route('author.chat.show', $conv);
    }
}
