<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// BookRejected
class BookRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Book   $book,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("❌ Livre non approuvé – {$this->book->title}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre livre **{$this->book->title}** n'a pas été approuvé pour la raison suivante :")
            ->line("> {$this->reason}")
            ->line('Vous pouvez corriger votre soumission et la renvoyer.')
            ->action('Modifier mon livre', url("/author/books"))
            ->line('Notre équipe reste disponible pour toute question.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'book_rejected',
            'book_id' => $this->book->id,
            'title'   => $this->book->title,
            'reason'  => $this->reason,
            'message' => "Votre livre « {$this->book->title} » a été rejeté.",
        ];
    }
}
