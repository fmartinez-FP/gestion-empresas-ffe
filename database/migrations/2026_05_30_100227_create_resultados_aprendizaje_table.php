<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_aprendizaje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos_profesionales');
            $table->string('codigo', 10);
            $table->text('descripcion');
            $table->boolean('activo_ffe')->default(false);
            $table->string('curso_academico_activo', 9)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_aprendizaje');
    }
};
