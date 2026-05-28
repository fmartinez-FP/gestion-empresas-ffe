<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empresa_ciclo', function (Blueprint $table) {
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos')->cascadeOnDelete();
            $table->boolean('acepta_primero')->default(false);
            $table->boolean('acepta_segundo')->default(true);
            $table->timestamps();
            
            $table->primary(['empresa_id', 'ciclo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_ciclo');
    }
};
