<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_asignacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['laborable', 'festivo', 'no_lectivo', 'baja'])->default('laborable');
            $table->string('motivo', 200)->nullable();
            $table->timestamps();

            $table->unique(['asignacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_asignacion');
    }
};
