<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// WithdrawalProcessed
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
