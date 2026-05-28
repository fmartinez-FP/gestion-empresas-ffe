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
        Schema::create('colocaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos')->cascadeOnDelete();
            $table->foreignId('registrado_por_id')->constrained('users')->cascadeOnUpdate();
            $table->string('curso_academico', 9); // Formato: 2024-2025
            $table->tinyInteger('numero_curso')->unsigned(); // 1 o 2
            $table->integer('num_alumnos')->unsigned();
            $table->integer('num_horas')->unsigned();
            $table->timestamps();
            
            $table->index('curso_academico');
            $table->index(['empresa_id', 'curso_academico']);
            $table->index(['ciclo_id', 'curso_academico']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colocaciones');
    }
};
