<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_asignacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->cascadeOnDelete();
            $table->enum('dia', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->time('entrada_manana');
            $table->time('salida_manana');
            $table->time('entrada_tarde')->nullable();
            $table->time('salida_tarde')->nullable();
            $table->timestamps();

            $table->unique(['asignacion_id', 'dia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_asignacion');
    }
};
