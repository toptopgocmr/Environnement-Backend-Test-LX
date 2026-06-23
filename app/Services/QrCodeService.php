<?php

namespace App\Services;

class QrCodeService
{
    public function generateForBook(int $bookId, string $slug): string
    {
        $url  = url('/books/' . $slug);
        $path = 'qrcodes/book-' . $bookId . '.svg';
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($url, storage_path('app/public/' . $path));
        return $path;
    }

    public function generateForOrder(string $orderNumber): string
    {
        $url  = url('/orders/' . $orderNumber . '/verify');
        $path = 'qrcodes/order-' . $orderNumber . '.svg';
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($url, storage_path('app/public/' . $path));
        return $path;
    }

    public function getUrl(string $path): string
    {
        return asset('storage/' . $path);
    }
}
