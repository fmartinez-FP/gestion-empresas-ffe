<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('valorado_por_id')->constrained('users')->onDelete('cascade');
            
            // Criterios de valoración (1-5 estrellas)
            $table->tinyInteger('trato_alumno')->unsigned()->default(0);
            $table->tinyInteger('calidad_formacion')->unsigned()->default(0);
            $table->tinyInteger('seguimiento_tutor')->unsigned()->default(0);
            $table->tinyInteger('comunicacion_ies')->unsigned()->default(0);
            $table->tinyInteger('posibilidad_contratacion')->unsigned()->default(0);
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            // Una valoración por empresa
            $table->unique('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
