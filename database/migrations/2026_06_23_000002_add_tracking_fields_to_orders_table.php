<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étendre l'enum shipping_status avant d'ajouter les colonnes
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_status ENUM('none','processing','shipped','out_for_delivery','delivered','failed','cancelled') NOT NULL DEFAULT 'none'");

        Schema::table('orders', function (Blueprint $table) {
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing('orders');
            if (!in_array('full_name', $cols))               $table->string('full_name')->nullable()->after('shipping_phone');
            if (!in_array('shipping_country', $cols))        $table->string('shipping_country', 5)->nullable()->default('CG')->after('full_name');
            if (!in_array('tracking_number', $cols))         $table->string('tracking_number')->nullable()->after('shipping_country');
            if (!in_array('carrier', $cols))                 $table->string('carrier')->nullable()->after('tracking_number');
            if (!in_array('shipping_note', $cols))           $table->text('shipping_note')->nullable()->after('carrier');
            if (!in_array('estimated_delivery_date', $cols)) $table->date('estimated_delivery_date')->nullable()->after('shipping_note');
            if (!in_array('shipped_at', $cols))              $table->timestamp('shipped_at')->nullable()->after('estimated_delivery_date');
            if (!in_array('delivered_at', $cols))            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });

        // Table des événements de suivi
        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');          // Statut au moment de l'événement
            $table->string('location')->nullable();   // Lieu (ex: "Brazzaville — Entrepôt central")
            $table->text('description');       // Description de l'événement
            $table->timestamp('occurred_at'); // Quand ça s'est passé
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_events');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['full_name','shipping_country','tracking_number','carrier','shipping_note','estimated_delivery_date','shipped_at','delivered_at']);
        });
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_status ENUM('none','processing','shipped','delivered') NOT NULL DEFAULT 'none'");
    }
};
