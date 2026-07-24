<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajoute 'peex' comme méthode de paiement possible (agrégateur mobile money)
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('mtn_momo','airtel_money','stripe','free','peex') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('mtn_momo','airtel_money','stripe','free') NULL");
    }
};
