<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivote para permitir que un responsable gestione múltiples ciclos
     */
    public function up(): void
    {
        Schema::create('ciclo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'ciclo_id']);
            $table->index('ciclo_id');
        });

        // Migrar datos existentes de ciclo_id a la nueva tabla
        DB::statement('
            INSERT INTO ciclo_user (user_id, ciclo_id, created_at, updated_at)
            SELECT id, ciclo_id, NOW(), NOW()
            FROM users
            WHERE ciclo_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciclo_user');
    }
};
