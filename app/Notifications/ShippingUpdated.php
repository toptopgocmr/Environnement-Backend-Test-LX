<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// ShippingUpdated
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
