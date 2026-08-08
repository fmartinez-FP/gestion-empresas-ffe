<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grupos formativos (1ºA, 1ºB, 2º...) dentro de un ciclo. Son fijos en el
     * tiempo -- no llevan curso_academico -- solo cambia quien los tutoriza
     * cada año (ver profesor_tutor). 'activo' permite deshabilitar un grupo
     * (o recuperar el ciclo entero) sin perder el historico de alumnos que
     * pertenecieron a el.
     */
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos')->onDelete('cascade');
            $table->unsignedTinyInteger('numero_curso'); // 1 o 2
            $table->string('etiqueta', 10)->default(''); // '', 'A', 'B'...
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['ciclo_id', 'numero_curso', 'etiqueta']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
