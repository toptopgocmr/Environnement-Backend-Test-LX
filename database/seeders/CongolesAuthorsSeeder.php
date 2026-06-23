<?php
// database/seeders/CongolesAuthorsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Book;

class CongolesAuthorsSeeder extends Seeder
{
    public function run(): void
    {
        $catId    = DB::table('categories')->where('slug', 'litterature-congolaise')->value('id')
                 ?? DB::table('categories')->where('sort_order', 5)->value('id');
        $catRoman = DB::table('categories')->where('slug', 'roman-fiction')->value('id')
                 ?? DB::table('categories')->where('sort_order', 1)->value('id');
        $catPoesie = DB::table('categories')->where('slug', 'poesie-theatre')->value('id')
                  ?? DB::table('categories')->where('sort_order', 2)->value('id');

        $authors = [
            // ── Alain Mabanckou ───────────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Alain Mabanckou',
                    'email'              => 'alain.mabanckou@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => 'Ne le 24 fevrier 1966 a Pointe-Noire (Republique du Congo), Alain Mabanckou est l\'un des ecrivains africains les plus celebres de sa generation. Romancier, poete et essayiste, il est professeur de litterature francophone a l\'Universite de Californie a Los Angeles (UCLA). Son oeuvre, traduite en une vingtaine de langues, explore avec humour et profondeur l\'identite africaine, le deracinement et la memoire. Il remporte le Prix Renaudot en 2006 pour Memoires de porc-epic et le Prix de la Langue francaise en 2012. Chevalier des Arts et Lettres, il est egalement membre de l\'Academie francaise depuis 2023.',
                    'country'            => 'CG',
                    'city'               => 'Pointe-Noire',
                    'domain'             => 'Roman - Poesie - Essai',
                    'website'            => 'https://alainmabanckou.net',
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Verre Casse',
                        'description'      => 'Dans un bar de Pointe-Noire, le Credit a voyage, un ivrogne nomme Verre Casse ecrit les histoires des habitues du bar. Un chef-d\'oeuvre de la litterature africaine contemporaine, ecrit dans un style oral et jouissif.',
                        'price'            => 3500,
                        'is_free'          => false,
                        'publication_year' => 2005,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 221,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => 'Black Bazar',
                        'description'      => 'Un Congolais a Paris, sapeur elegant, dont la compagne vient de partir avec un autre. Il decide d\'ecrire sa vengeance. Un roman vibrant sur l\'identite africaine en France.',
                        'price'            => 3500,
                        'is_free'          => false,
                        'publication_year' => 2009,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 237,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => 'Memoires de porc-epic',
                        'description'      => 'Prix Renaudot 2006. Un porc-epic raconte comment il est devenu le double malfique d\'un humain. Un roman fable qui mele magie africaine et reflexion sur le bien et le mal.',
                        'price'            => 3500,
                        'is_free'          => false,
                        'publication_year' => 2006,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 151,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Sony Labou Tansi ──────────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Sony Labou Tansi',
                    'email'              => 'sony.labou.tansi@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => 'Sony Labou Tansi, de son vrai nom Marcel Ntsoni, nait le 5 juillet 1947 a Kimwanza (Congo) et meurt le 14 juin 1995 a Brazzaville. Dramaturge, romancier et poete, il est l\'une des figures les plus originales et subversives de la litterature africaine du XXe siecle. Fondateur du Rocado Zulu Theatre en 1979 a Brazzaville, il developpe une ecriture baroque et visionnaire, denoncant avec force les dictatures postcoloniales. Son roman La Vie et Demie (1979) est considere comme un chef-d\'oeuvre de la litterature africaine. Il fut egalement depute a l\'Assemblee nationale du Congo.',
                    'country'            => 'CG',
                    'city'               => 'Brazzaville',
                    'domain'             => 'Roman - Theatre - Poesie',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'La Vie et Demie',
                        'description'      => 'Roman dystopique d\'une nation africaine gouvernee par un dictateur impitoyable. Un chef-d\'oeuvre de la litterature postcoloniale africaine, ecrit dans une langue inventive et visionnaire.',
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1979,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 197,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => "L'Etat honteux",
                        'description'      => 'Dans un pays imaginaire d\'Afrique centrale, un dictateur grotesque regne par la terreur. Un roman hallucine et visionnaire sur le pouvoir absolu et la deraison politique.',
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1981,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 179,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => 'Les Sept Solitudes de Lorsa Lopez',
                        'description'      => 'Dans la ville de Nsanga-Norda, une femme est assassinee. Son mari, Lorsa Lopez, refuse de l\'enterrer. Un roman poetique sur le deuil, la resistance et la memoire.',
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1985,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 188,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Henri Lopes ───────────────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Henri Lopes',
                    'email'              => 'henri.lopes@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => 'Henri Lopes nait le 12 septembre 1937 a Leopoldville (Kinshasa). Romancier, essayiste et homme d\'Etat congolais, il occupe les plus hautes fonctions politiques au Congo : ministre de l\'Education nationale, ministre des Affaires etrangeres, Premier ministre (1973-1975), puis Directeur general adjoint de l\'UNESCO de 1998 a 2011. Son oeuvre romanesque explore le metissage culturel, l\'identite et la politique en Afrique. Il est membre associe de l\'Academie royale de langue et de litterature francaises de Belgique.',
                    'country'            => 'CG',
                    'city'               => 'Brazzaville',
                    'domain'             => 'Roman - Politique',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Le Pleurer-Rire',
                        'description'      => 'La satire d\'un regime dictatorial en Afrique sub-saharienne, racontee par un aide de camp cynique. Un roman majeur qui denonce avec humour et amertume les abus du pouvoir.',
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1982,
                        'publisher'        => 'Presence Africaine',
                        'pages'            => 309,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => 'Le Lys et le Flamboyant',
                        'description'      => 'L\'histoire d\'une chanteuse metisse entre deux mondes, l\'Afrique et l\'Europe. Un roman sur la quete identitaire et l\'amour impossible entre deux cultures.',
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1997,
                        'publisher'        => 'Editions du Seuil',
                        'pages'            => 378,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Tchicaya U Tam'si ─────────────────────────────────────────────
            [
                'user' => [
                    'name'               => "Tchicaya U Tam'si",
                    'email'              => 'tchicaya.utamsi@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => "Gerald-Felix Tchicaya, dit Tchicaya U Tam'si (petite feuille qui parle pour son pays), nait le 25 aout 1931 a Mpili (Congo) et meurt le 22 avril 1988 a Bazancourt (France). Considere comme l'un des plus grands poetes africains du XXe siecle, il grandit a Paris des l'age de 15 ans. Son oeuvre poetique est profondement enracinee dans la tradition congolaise et l'experience de l'exil. Il fut aussi nouvelliste, dramaturge et journaliste a l'UNESCO.",
                    'country'            => 'CG',
                    'city'               => 'Mpili',
                    'domain'             => 'Poesie - Roman - Theatre',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Le Mauvais Sang',
                        'description'      => "Recueil de poemes fondateur de l'oeuvre de Tchicaya U Tam'si, publie en 1955. Une poesie enracinee dans la douleur de la colonisation et la quete d'identite africaine.",
                        'price'            => 2500,
                        'is_free'          => false,
                        'publication_year' => 1955,
                        'publisher'        => 'Pierre Jean Oswald',
                        'pages'            => 78,
                        'category_id'      => $catPoesie ?? $catId,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => "Ces Fruits si doux de l'arbre a pain",
                        'description'      => "Son dernier roman, publie a titre posthume. L'histoire de Prosper, un Congolais de retour au pays, confronte a la corruption et a la desillusion postcoloniale.",
                        'price'            => 3000,
                        'is_free'          => false,
                        'publication_year' => 1987,
                        'publisher'        => 'Seghers',
                        'pages'            => 221,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Emmanuel Dongala ──────────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Emmanuel Dongala',
                    'email'              => 'emmanuel.dongala@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => "Emmanuel Boundzeki Dongala nait en 1941 a Alindao (Republique centrafricaine) de parents congolais. Romancier, nouvelliste et chimiste de formation, il enseigne la chimie a Brazzaville puis aux Etats-Unis apres avoir fui la guerre civile congolaise de 1997. Ses romans temoignent avec une puissance emotionnelle rare des violences politiques et des guerres civiles en Afrique. Johnny Chien Mechant (2002), traduit en plusieurs langues et adapte au cinema, lui vaut une reconnaissance internationale. Il est professeur emerite au Simon's Rock College (Bard College, Massachusetts).",
                    'country'            => 'CG',
                    'city'               => 'Brazzaville',
                    'domain'             => 'Roman - Nouvelle',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Johnny Chien Mechant',
                        'description'      => "Dans un pays africain ravage par la guerre civile, deux adolescents tentent de survivre. Un roman bouleversant sur les enfants-soldats et les victimes civiles des conflits armes africains.",
                        'price'            => 3500,
                        'is_free'          => false,
                        'publication_year' => 2002,
                        'publisher'        => 'Le Serpent a Plumes',
                        'pages'            => 285,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                    [
                        'title'            => 'Jazz et vin de palme',
                        'description'      => "Recueil de nouvelles qui mele la musique, la vie quotidienne africaine et la quete de liberte. Un livre joyeux et tragique a la fois, reflet de l'ame congolaise.",
                        'price'            => 2500,
                        'is_free'          => false,
                        'publication_year' => 1982,
                        'publisher'        => 'Hatier International',
                        'pages'            => 156,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Fiston Mwanza Mujila ──────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Fiston Mwanza Mujila',
                    'email'              => 'fiston.mwanza@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => "Fiston Mwanza Mujila nait en 1981 a Lubumbashi (Republique Democratique du Congo). Poete, romancier et dramaturge, il vit a Graz (Autriche). Son roman Tram 83 (2014), portrait d'une ville-mine africaine a travers le prisme d'un bar mythique, est une oeuvre-jazz qui revolutionne la litterature africaine contemporaine. Il recoit le Prix des Cinq Continents de la Francophonie et le Prix Etisalat de litterature africaine. Son ecriture, influencee par le jazz et la tradition orale, cree un idiome litteraire entierement nouveau.",
                    'country'            => 'CD',
                    'city'               => 'Lubumbashi',
                    'domain'             => 'Roman - Poesie',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Tram 83',
                        'description'      => "Dans une ville-mine africaine, le Tram 83 est le seul bar ou tout le monde se retrouve : prostituees, mineurs, touristes, intellectuels. Un roman-jazz, une ode a la survie et a la liberte.",
                        'price'            => 3500,
                        'is_free'          => false,
                        'publication_year' => 2014,
                        'publisher'        => 'Metailie',
                        'pages'            => 205,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => true,
                        'status'           => 'published',
                    ],
                ],
            ],

            // ── Basile Marius Ngassaki ────────────────────────────────────────
            [
                'user' => [
                    'name'               => 'Basile Marius Ngassaki',
                    'email'              => 'b.ngassaki@lirex.africa',
                    'password'           => Hash::make('LireX2024!'),
                    'role'               => 'author',
                    'bio'                => "Ne le 12 fevrier 1954 a Brazzaville (Republique du Congo), Basile Marius Ngassaki est enseignant-chercheur a la Faculte des Lettres, Arts et Sciences Humaines (FLASH) de l'Universite Marien Ngouabi de Brazzaville. Professeur CAMES, specialiste de litterature comparee anglophone et africaine, il consacre ses recherches a l'identite culturelle, la tradition orale et la litterature postcoloniale. Ses travaux portent notamment sur des oeuvres majeures de la litterature mondiale telles que Song of Solomon de Toni Morrison et Things Fall Apart de Chinua Achebe. Ses articles sont publies dans des revues scientifiques internationales a comite de lecture.",
                    'country'            => 'CG',
                    'city'               => 'Brazzaville',
                    'domain'             => 'Litterature comparee - Etudes postcoloniales',
                    'website'            => null,
                    'is_verified_author' => true,
                    'is_active'          => true,
                ],
                'books' => [
                    [
                        'title'            => 'Cultural Identity in Morrison and Achebe',
                        'description'      => "Article scientifique publie dans l'International Journal of Linguistics, Literature and Translation (Vol. 6, N.11, 2023). Cette etude compare la representation de l'identite culturelle dans Song of Solomon de Toni Morrison et Things Fall Apart de Chinua Achebe, a travers les approches sociologique, historique et linguistique.",
                        'price'            => 0,
                        'is_free'          => true,
                        'publication_year' => 2023,
                        'publisher'        => 'IJLLT Vol.6 N.11',
                        'pages'            => 9,
                        'category_id'      => $catId ?? $catRoman,
                        'is_featured'      => false,
                        'status'           => 'published',
                    ],
                ],
            ],
        ];

        foreach ($authors as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], ['email_verified_at' => now()])
            );

            foreach ($data['books'] as $bookData) {
                $slug = Str::slug($bookData['title']) . '-' . Str::lower(Str::substr($user->name, 0, 4));
                Book::updateOrCreate(
                    ['slug' => $slug],
                    array_merge($bookData, [
                        'author_id'      => $user->id,
                        'slug'           => $slug,
                        'language'       => 'fr',
                        'currency'       => 'XAF',
                        'format'         => 'pdf',
                        'views'          => rand(120, 2400),
                        'downloads'      => rand(15, 380),
                        'average_rating' => round(rand(38, 50) / 10, 1),
                        'ratings_count'  => rand(8, 95),
                    ])
                );
            }
        }

        $this->command->info('OK: ' . count($authors) . ' auteurs congolais seedes avec leurs livres.');
    }
}
