<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajuste_horas_semana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->cascadeOnDelete();
            $table->date('semana');
            $table->decimal('ajuste', 4, 2)->default(0);
            $table->text('motivo')->nullable();
            $table->foreignId('created_by_id')->constrained('users');
            $table->timestamps();

            $table->unique(['asignacion_id', 'semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajuste_horas_semana');
    }
};
