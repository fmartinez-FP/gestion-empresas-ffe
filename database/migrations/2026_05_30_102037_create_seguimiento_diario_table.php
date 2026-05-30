<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_diario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('descripcion_tareas')->nullable();
            $table->string('evidencia_path', 500)->nullable();
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->boolean('confirmado_tutor')->default(false);
            $table->timestamp('confirmado_at')->nullable();
            $table->text('comentario_tutor')->nullable();
            $table->timestamps();

            $table->unique(['asignacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_diario');
    }
};
