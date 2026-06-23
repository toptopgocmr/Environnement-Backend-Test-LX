<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('domain')->nullable()->after('bio')
                ->comment('Domaine / spécialité littéraire de l\'auteur');
            $table->string('website')->nullable()->after('domain');
            $table->string('mtn_number', 20)->nullable()->after('website');
            $table->string('airtel_number', 20)->nullable()->after('mtn_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['domain', 'website', 'mtn_number', 'airtel_number']);
        });
    }
};
