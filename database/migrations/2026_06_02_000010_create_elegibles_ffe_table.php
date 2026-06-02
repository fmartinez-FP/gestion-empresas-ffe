<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elegibles_ffe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultado_aprendizaje_id')
                  ->constrained('resultados_aprendizaje')
                  ->cascadeOnDelete();
            $table->string('curso_academico', 9);
            $table->foreignId('created_by_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['resultado_aprendizaje_id', 'curso_academico'], 'elegible_ra_curso_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elegibles_ffe');
    }
};
