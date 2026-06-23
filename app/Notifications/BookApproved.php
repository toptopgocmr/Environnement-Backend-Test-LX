<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// BookApproved
class BookApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Book $book) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("✅ Votre livre est publié – {$this->book->title}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Excellente nouvelle ! Votre livre **{$this->book->title}** vient d'être approuvé et est maintenant disponible sur LireX.")
            ->action('Voir mon livre', url("/books/{$this->book->slug}"))
            ->line('Merci de contribuer au savoir africain 🌍');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'book_approved',
            'book_id'  => $this->book->id,
            'title'    => $this->book->title,
            'message'  => "Votre livre « {$this->book->title} » a été approuvé et publié.",
        ];
    }
}
