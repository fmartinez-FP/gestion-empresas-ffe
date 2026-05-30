<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_fct', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct');
            $table->enum('tipo', ['plan_formativo', 'ficha_seguimiento', 'informe_final']);
            $table->string('archivo_path', 500)->nullable();
            $table->timestamp('generado_at')->nullable();
            $table->boolean('firmado')->default(false);
            $table->timestamp('firmado_at')->nullable();
            $table->string('archivo_firmado_path', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_fct');
    }
};
