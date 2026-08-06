<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_formativo_datos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')
                  ->unique()
                  ->constrained('asignaciones_fct')
                  ->cascadeOnDelete();
            $table->boolean('medidas_discapacidad')->default(false);
            $table->text('medidas_discapacidad_detalle')->nullable();
            $table->boolean('autorizacion_extraordinaria')->default(false);
            $table->text('autorizacion_extraordinaria_detalle')->nullable();
            $table->enum('intervalo', ['diario','semanal','mensual','otros','varias_empresas'])->default('diario');
            $table->text('periodos')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('formaciones_especificas')->nullable();
            $table->json('imparticion_modulos')->nullable();
            $table->date('purgar_after');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_formativo_datos');
    }
};
