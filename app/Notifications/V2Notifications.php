<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// ═══════════════════════════════════════════════════════════
// AccountApproved
// ═══════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════
// AccountRejected
// ═══════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════
// ShippingUpdated
// ═══════════════════════════════════════════════════════════
class ShippingUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public readonly \App\Models\Order $order) {}

    public function via(object $notifiable): array { return ['database', 'mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $labels = [
            'processing' => ['🔄 Commande en préparation', 'Votre commande est en cours de préparation.'],
            'shipped'    => ['📦 Commande expédiée',        "Votre commande a été expédiée. Numéro de suivi : {$this->order->tracking_number}"],
            'delivered'  => ['✅ Commande livrée',           'Votre commande a été livrée. Bonne lecture !'],
        ];
        [$subject, $line] = $labels[$this->order->shipping_status] ?? ['Mise à jour commande', 'Votre commande a été mise à jour.'];

        return (new MailMessage)
            ->subject($subject . ' – ' . $this->order->book->title)
            ->greeting("Bonjour {$notifiable->name},")
            ->line($line)
            ->when($this->order->carrier, fn($m) => $m->line("Transporteur : {$this->order->carrier}"))
            ->action('Voir ma commande', url('/orders'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'shipping_updated',
            'order_id'         => $this->order->id,
            'shipping_status'  => $this->order->shipping_status,
            'tracking_number'  => $this->order->tracking_number,
            'book_title'       => $this->order->book->title ?? '',
            'message'          => "Votre commande « {$this->order->book->title} » : {$this->order->shipping_status}",
        ];
    }
}
