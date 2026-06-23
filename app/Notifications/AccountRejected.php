<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// AccountRejected
class AccountRejected extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public readonly string $reason) {}

    public function via(object $notifiable): array { return ['database', 'mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("❌ Demande de compte non approuvée – LireX")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre demande de compte n'a pas pu être approuvée.")
            ->line("Raison : {$this->reason}")
            ->line("Vous pouvez soumettre une nouvelle demande avec les informations corrigées.")
            ->action('Soumettre une nouvelle demande', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'account_rejected',
            'reason'  => $this->reason,
            'message' => "Votre demande de compte a été rejetée.",
        ];
    }
}
