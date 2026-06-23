<?php
/**
 * Script one-shot — lier le PDF du livre 14 (Ngassaki)
 * Exécuter depuis le dossier backend/ :
 *   php update_book14_file.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;

$book = Book::find(14);
if (!$book) {
    echo "Livre 14 introuvable.\n";
    exit(1);
}

$filePath = 'books/ngassaki_cultural_identity_2023.pdf';
$fullPath = storage_path('app/' . $filePath);

if (!file_exists($fullPath)) {
    echo "Fichier PDF introuvable : {$fullPath}\n";
    exit(1);
}

$book->update([
    'file_path' => $filePath,
    'format'    => 'pdf',
    'pages'     => 9,   // article 9 pages selon les metadonnees
]);

echo "OK — Livre 14 mis a jour:\n";
echo "  Titre    : {$book->title}\n";
echo "  file_path: {$filePath}\n";
echo "  Taille   : " . round(filesize($fullPath) / 1024) . " Ko\n";
