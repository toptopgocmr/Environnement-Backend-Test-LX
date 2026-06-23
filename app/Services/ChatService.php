<?php
namespace App\Services;

use App\Models\{ChatConversation, ChatMessage, ChatParticipant, User, Book, Order, PhysicalStockMovement};
use Illuminate\Support\Facades\{DB, Event};
use App\Events\MessageSent;

// ─────────────────────────────────────────────────────────────────────────────
// CHAT SERVICE
// ─────────────────────────────────────────────────────────────────────────────
class ChatService
{
    /**
     * Ouvre ou retrouve la conversation lecteur ↔ auteur depuis le panier.
     */
    public function getOrCreateReaderAuthorConversation(
        int $readerId,
        int $authorId,
        int $bookId,
        ?int $orderId = null
    ): ChatConversation {
        // Cherche une conversation existante
        $existing = ChatConversation::where('type', 'reader_author')
            ->where('book_id', $bookId)
            ->whereHas('participants', fn($q) => $q->where('user_id', $readerId))
            ->whereHas('participants', fn($q) => $q->where('user_id', $authorId))
            ->where('status', 'open')
            ->first();

        if ($existing) return $existing;

        return DB::transaction(function () use ($readerId, $authorId, $bookId, $orderId) {
            $book = Book::find($bookId);
            $conv = ChatConversation::create([
                'type'     => 'reader_author',
                'book_id'  => $bookId,
                'order_id' => $orderId,
                'subject'  => 'Discussion sur : ' . ($book->title ?? ''),
                'status'   => 'open',
                'last_message_at' => now(),
            ]);

            ChatParticipant::create(['conversation_id' => $conv->id, 'user_id' => $readerId]);
            ChatParticipant::create(['conversation_id' => $conv->id, 'user_id' => $authorId]);

            // Message système d'ouverture
            ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $readerId,
                'body'            => "👋 Bonjour, j'ai ajouté votre livre « {$book->title} » à mon panier et j'aimerais vous poser quelques questions.",
                'type'            => 'system',
            ]);

            return $conv;
        });
    }

    /**
     * Ouvre ou retrouve une conversation admin ↔ user.
     */
    public function getOrCreateAdminConversation(
        int $adminId,
        int $userId,
        string $type = 'admin_author',
        ?string $subject = null
    ): ChatConversation {
        $existing = ChatConversation::where('type', $type)
            ->whereHas('participants', fn($q) => $q->where('user_id', $adminId))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'open')
            ->first();

        if ($existing) return $existing;

        return DB::transaction(function () use ($adminId, $userId, $type, $subject) {
            $conv = ChatConversation::create([
                'type'           => $type,
                'subject'        => $subject ?? 'Support LireX',
                'status'         => 'open',
                'last_message_at'=> now(),
            ]);
            ChatParticipant::create(['conversation_id' => $conv->id, 'user_id' => $adminId]);
            ChatParticipant::create(['conversation_id' => $conv->id, 'user_id' => $userId]);
            return $conv;
        });
    }

    /**
     * Envoie un message dans une conversation.
     */
    public function sendMessage(
        ChatConversation $conversation,
        int $senderId,
        string $body,
        string $type = 'text',
        ?string $filePath = null,
        ?string $fileName = null
    ): ChatMessage {
        $message = DB::transaction(function () use ($conversation, $senderId, $body, $type, $filePath, $fileName) {
            $msg = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $senderId,
                'body'            => $body,
                'type'            => $type,
                'file_path'       => $filePath,
                'file_name'       => $fileName,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $msg;
        });

        // Broadcast WebSocket (si Pusher configuré)
        try {
            event(new MessageSent($message->load('sender')));
        } catch (\Exception $e) {
            \Log::warning('Broadcast failed: ' . $e->getMessage());
        }

        return $message;
    }

    /**
     * Marque tous les messages comme lus pour un utilisateur.
     */
    public function markAsRead(ChatConversation $conversation, int $userId): void
    {
        $conversation->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Liste les conversations d'un utilisateur avec le dernier message.
     */
    public function getUserConversations(int $userId): \Illuminate\Support\Collection
    {
        return ChatConversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with([
                'lastMessage.sender:id,name,avatar',
                'participants.user:id,name,avatar,role',
                'book:id,title,cover_image',
            ])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->get();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// PHYSICAL STOCK SERVICE
// ─────────────────────────────────────────────────────────────────────────────
class PhysicalStockService
{
    /**
     * Ajoute du stock pour un livre.
     */
    public function addStock(int $bookId, int $quantity, string $reason = 'Réception', int $userId = null): void
    {
        DB::transaction(function () use ($bookId, $quantity, $reason, $userId) {
            $book = \App\Models\Book::lockForUpdate()->findOrFail($bookId);
            $book->increment('physical_stock', $quantity);

            PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'in',
                'quantity'    => $quantity,
                'stock_after' => $book->physical_stock,
                'reason'      => $reason,
                'created_by'  => $userId,
            ]);
        });
    }

    /**
     * Réserve du stock pour une commande physique.
     */
    public function reserveStock(int $bookId, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($bookId, $quantity) {
            $book = \App\Models\Book::lockForUpdate()->findOrFail($bookId);
            if ($book->physical_stock < $quantity) return false;

            $book->decrement('physical_stock', $quantity);

            PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'out',
                'quantity'    => $quantity,
                'stock_after' => $book->physical_stock,
                'reason'      => 'Commande physique',
            ]);

            return true;
        });
    }

    /**
     * Retourne du stock (annulation commande).
     */
    public function returnStock(int $bookId, int $quantity = 1, string $reason = 'Annulation commande'): void
    {
        DB::transaction(function () use ($bookId, $quantity, $reason) {
            $book = \App\Models\Book::lockForUpdate()->findOrFail($bookId);
            $book->increment('physical_stock', $quantity);

            PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'in',
                'quantity'    => $quantity,
                'stock_after' => $book->physical_stock,
                'reason'      => $reason,
            ]);
        });
    }

    /**
     * Vérifie la disponibilité.
     */
    public function isAvailable(int $bookId, int $quantity = 1): bool
    {
        $book = \App\Models\Book::find($bookId);
        return $book && $book->physical_stock >= $quantity;
    }
}
