<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir campo observaciones a colocaciones
     */
    public function up(): void
    {
        Schema::table('colocaciones', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('num_horas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colocaciones', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
