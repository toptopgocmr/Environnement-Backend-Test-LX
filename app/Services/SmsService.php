<?php

namespace App\Services;

use App\Models\{Book, Order, Royalty, User};
use Illuminate\Support\Facades\{Http, DB};
use Illuminate\Support\Str;

// SMS SERVICE (via Africa's Talking or similar)
class SmsService
{
    public function send(string $phone, string $message): bool
    {
        try {
            $res = Http::withBasicAuth(config('services.sms.username'), config('services.sms.api_key'))
                ->post(config('services.sms.url', 'https://api.africastalking.com/version1/messaging'), [
                    'username' => config('services.sms.username'),
                    'to'       => $phone,
                    'message'  => $message,
                    'from'     => 'LireX',
                ]);
            return $res->successful();
        } catch (\Exception $e) {
            \Log::error('SMS error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        return $this->send($phone, "LireX: Votre code de vérification est {$otp}. Valable 10 minutes.");
    }

    public function sendBookApproved(string $phone, string $bookTitle): bool
    {
        return $this->send($phone, "LireX: Votre livre « {$bookTitle} » vient d'être approuvé et est maintenant en ligne !");
    }
}
