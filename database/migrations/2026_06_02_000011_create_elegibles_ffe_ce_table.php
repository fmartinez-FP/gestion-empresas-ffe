<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elegibles_ffe_ce', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterio_evaluacion_id')
                  ->constrained('criterios_evaluacion')
                  ->cascadeOnDelete();
            $table->string('curso_academico', 9);
            $table->foreignId('created_by_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['criterio_evaluacion_id', 'curso_academico'], 'elegible_ce_curso_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elegibles_ffe_ce');
    }
};
