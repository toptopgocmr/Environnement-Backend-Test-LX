<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AiReview, AccountRequest, Book, Order, User, ChatConversation, PhysicalStockMovement};
use App\Services\{AiReviewService, ChatService, PhysicalStockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ADMIN — CHAT
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
        $msg = $this->chatService->sendMessage($conversation, auth()->id(), $request->body);

        if ($request->expectsJson()) {
            $msg->load('sender:id,name,avatar');
            return response()->json([
                'id'         => $msg->id,
                'body'       => $msg->body,
                'sender_id'  => $msg->sender_id,
                'sender'     => $msg->sender->name ?? '',
                'created_at' => $msg->created_at->format('d/m H:i'),
                'type'       => $msg->type ?? 'text',
            ]);
        }

        return back()->with('success', 'Message envoyé.');
    }

    /** Polling — retourne les messages après un certain ID */
    public function pollMessages(Request $request, ChatConversation $conversation)
    {
        $lastId = (int) $request->get('last_id', 0);
        $msgs   = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender->name ?? '',
                'created_at' => $m->created_at->format('d/m H:i'),
                'type'       => $m->type ?? 'text',
            ]);

        $this->chatService->markAsRead($conversation, auth()->id());

        return response()->json(['messages' => $msgs]);
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
