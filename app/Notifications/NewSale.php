<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// NewSale
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
