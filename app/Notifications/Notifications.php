<?php
namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// ═══════════════════════════════════════════════════════════
// BookApproved
// ═══════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════
// BookRejected
// ═══════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════
// NewSale
// ═══════════════════════════════════════════════════════════
class NewSale extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly \App\Models\Order   $order,
        public readonly \App\Models\Royalty $royalty,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $net = number_format($this->royalty->net_amount, 0, ',', ' ');
        return [
            'type'       => 'new_sale',
            'order_id'   => $this->order->id,
            'book_title' => $this->order->book->title ?? '',
            'amount'     => $this->royalty->net_amount,
            'currency'   => $this->royalty->currency,
            'message'    => "Nouvelle vente ! Vous avez gagné {$net} XAF.",
        ];
    }
}

// ═══════════════════════════════════════════════════════════
// WithdrawalProcessed
// ═══════════════════════════════════════════════════════════
class WithdrawalProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly \App\Models\WithdrawalRequest $withdrawal,
        public readonly string                         $status, // 'completed' | 'rejected'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->withdrawal->amount, 0, ',', ' ');

        if ($this->status === 'completed') {
            return (new MailMessage)
                ->subject("💰 Retrait de {$amount} XAF traité")
                ->greeting("Bonjour {$notifiable->name},")
                ->line("Votre demande de retrait de **{$amount} XAF** a été traitée avec succès.")
                ->line("Les fonds ont été envoyés sur votre compte {$this->withdrawal->method}.")
                ->action('Voir mes gains', url('/author/earnings'));
        }

        return (new MailMessage)
            ->subject("⚠️ Retrait rejeté")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre demande de retrait de **{$amount} XAF** a été rejetée.")
            ->line("Raison : {$this->withdrawal->rejection_reason}")
            ->action('Contacter le support', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->withdrawal->amount, 0, ',', ' ');
        return [
            'type'    => 'withdrawal_' . $this->status,
            'amount'  => $this->withdrawal->amount,
            'method'  => $this->withdrawal->method,
            'message' => $this->status === 'completed'
                ? "Retrait de {$amount} XAF traité avec succès."
                : "Votre retrait de {$amount} XAF a été rejeté.",
        ];
    }
}
