<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Littérature & Fiction
            ['name' => 'Roman & Fiction',          'icon' => '📖', 'color' => '#6366f1', 'sort_order' => 1],
            ['name' => 'Poésie & Théâtre',         'icon' => '🎭', 'color' => '#8b5cf6', 'sort_order' => 2],
            ['name' => 'Nouvelles & Contes',        'icon' => '📜', 'color' => '#a855f7', 'sort_order' => 3],
            ['name' => 'Littérature africaine',     'icon' => '🌍', 'color' => '#f59e0b', 'sort_order' => 4],
            ['name' => 'Littérature congolaise',    'icon' => '🇨🇬', 'color' => '#10b981', 'sort_order' => 5],
            ['name' => 'Roman historique',          'icon' => '🏛️', 'color' => '#dc2626', 'sort_order' => 6],
            ['name' => 'Biographie & Mémoires',    'icon' => '🧑‍💼', 'color' => '#0ea5e9', 'sort_order' => 7],

            // Sciences humaines & sociales
            ['name' => 'Sciences sociales',         'icon' => '🤝', 'color' => '#0891b2', 'sort_order' => 8],
            ['name' => 'Histoire & Civilisations',  'icon' => '🏺', 'color' => '#b45309', 'sort_order' => 9],
            ['name' => 'Philosophie & Éthique',     'icon' => '🧠', 'color' => '#7c3aed', 'sort_order' => 10],
            ['name' => 'Religion & Spiritualité',   'icon' => '🕊️', 'color' => '#059669', 'sort_order' => 11],
            ['name' => 'Politique & Gouvernance',   'icon' => '🏛️', 'color' => '#1d4ed8', 'sort_order' => 12],

            // Sciences & Technologie
            ['name' => 'Informatique & Numérique',  'icon' => '💻', 'color' => '#2563eb', 'sort_order' => 13],
            ['name' => 'Sciences & Nature',          'icon' => '🔬', 'color' => '#16a34a', 'sort_order' => 14],
            ['name' => 'Mathématiques',              'icon' => '📐', 'color' => '#ca8a04', 'sort_order' => 15],
            ['name' => 'Médecine & Santé',           'icon' => '⚕️', 'color' => '#ef4444', 'sort_order' => 16],

            // Développement & Pratique
            ['name' => 'Développement personnel',   'icon' => '🚀', 'color' => '#f97316', 'sort_order' => 17],
            ['name' => 'Économie & Business',        'icon' => '📈', 'color' => '#0d9488', 'sort_order' => 18],
            ['name' => 'Droit & Justice',            'icon' => '⚖️', 'color' => '#475569', 'sort_order' => 19],
            ['name' => 'Éducation & Pédagogie',     'icon' => '🎓', 'color' => '#7c3aed', 'sort_order' => 20],

            // Jeunesse & Culture
            ['name' => 'Jeunesse & Enfants',        'icon' => '🧒', 'color' => '#fb923c', 'sort_order' => 21],
            ['name' => 'Culture & Traditions',      'icon' => '🪘', 'color' => '#b45309', 'sort_order' => 22],
            ['name' => 'Art & Photographie',        'icon' => '🎨', 'color' => '#ec4899', 'sort_order' => 23],
            ['name' => 'Cuisine & Gastronomie',     'icon' => '🍽️', 'color' => '#84cc16', 'sort_order' => 24],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['is_active' => true])
            );
        }

        $this->command->info(count($categories) . ' catégories créées/vérifiées.');
    }
}
