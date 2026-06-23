<?php
// database/seeders/BookCoversSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookCoversSeeder extends Seeder
{
    public function run(): void
    {
        $covers = [
            'verre-casse-alai'          => '/covers/verre-casse-alai.svg',
            'black-bazar-alai'          => '/covers/black-bazar-alai.svg',
            'memoires-de-porc-epic-alai'=> '/covers/memoires-de-porc-epic-alai.svg',
            'la-vie-et-demie-sony'      => '/covers/la-vie-et-demie-sony.svg',
            'l-etat-honteux-sony'       => '/covers/l-etat-honteux-sony.svg',
            'les-sept-solitudes-de-lorsa-lopez-sony' => '/covers/les-sept-solitudes-de-lorsa-lopez-sony.svg',
            'le-pleurer-rire-henr'      => '/covers/le-pleurer-rire-henr.svg',
            'le-lys-et-le-flamboyant-henr' => '/covers/le-lys-et-le-flamboyant-henr.svg',
            'le-mauvais-sang-tchi'      => '/covers/le-mauvais-sang-tchi.svg',
            'ces-fruits-si-doux-de-l-arbre-a-pain-tchi' => '/covers/ces-fruits-si-doux-de-l-arbre-a-pain-tchi.svg',
            'johnny-chien-mechant-emma' => '/covers/johnny-chien-mechant-emma.svg',
            'jazz-et-vin-de-palme-emma' => '/covers/jazz-et-vin-de-palme-emma.svg',
            'tram-83-fist'              => '/covers/tram-83-fist.svg',
            'a-comparative-approach-of-the-portrayal-of-the-cultural-identity-in-toni-morrison-and-chinua-achebe-basi'
                => '/covers/a-comparative-approach-of-the-portrayal-of-the-cultural-identity-in-toni-morrison-and-chinua-achebe-basi.svg',
        ];

        $updated = 0;
        foreach ($covers as $slug => $path) {
            $rows = Book::where('slug', $slug)->update(['cover_image' => $path]);
            $updated += $rows;
        }

        $this->command->info("✓ {$updated} couvertures mises à jour.");
    }
}
