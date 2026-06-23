<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('zone');               // 'congo' | 'international'
            $table->string('label');              // Affichage : "Congo (Brazzaville)", "Étranger"
            $table->unsignedBigInteger('base_price');   // XAF — frais fixes
            $table->unsignedBigInteger('price_per_kg')->default(0); // XAF par kg supplémentaire
            $table->unsignedInteger('free_above')->default(0);      // gratuit si commande > X XAF (0=jamais)
            $table->unsignedInteger('estimated_days_min')->default(2);
            $table->unsignedInteger('estimated_days_max')->default(7);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Valeurs par défaut
        DB::table('shipping_rates')->insert([
            [
                'zone'               => 'congo',
                'label'              => 'Congo-Brazzaville',
                'base_price'         => 1500,
                'price_per_kg'       => 0,
                'free_above'         => 25000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'is_active'          => true,
                'notes'              => 'Livraison locale — gratuite dès 25 000 XAF',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'zone'               => 'international',
                'label'              => 'International (hors Congo)',
                'base_price'         => 8000,
                'price_per_kg'       => 2500,
                'free_above'         => 0,
                'estimated_days_min' => 7,
                'estimated_days_max' => 21,
                'is_active'          => true,
                'notes'              => 'Tarif Afrique, Europe et reste du monde',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
