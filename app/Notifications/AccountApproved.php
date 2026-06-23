<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// AccountApproved
class AccountApproved extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public readonly string $type) {}

    public function via(object $notifiable): array { return ['database', 'mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $label = match($this->type) {
            'author'   => 'auteur',
            'auditor'  => 'auditeur',
            default    => 'institution',
        };
        return (new MailMessage)
            ->subject("✅ Votre compte {$label} a été activé – LireX")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Bonne nouvelle ! Votre demande de compte **{$label}** a été approuvée.")
            ->line("Vous pouvez maintenant vous connecter et accéder à toutes les fonctionnalités de votre espace.")
            ->action('Accéder à mon espace', url('/'))
            ->line('Bienvenue sur LireX 🎉');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'account_approved',
            'role'    => $this->type,
            'message' => "Votre compte a été activé. Bienvenue sur LireX !",
        ];
    }
}
