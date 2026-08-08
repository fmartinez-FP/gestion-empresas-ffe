<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivote profesor↔ciclo↔curso_academico. A diferencia de ciclo_user
     * (indefinida en el tiempo, para responsable_ciclo), esta asignación es
     * anual: el admin/responsable_ffe reasigna ciclo+curso a cada profesor
     * al inicio de cada curso académico. No se trunca en ffe:reset-curso —
     * el histórico se conserva y el filtro por curso vigente es responsabilidad
     * de la query, no de un borrado físico.
     */
    public function up(): void
    {
        Schema::create('profesor_tutor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos')->onDelete('cascade');
            $table->string('curso_academico', 9);
            $table->timestamps();

            $table->unique(['user_id', 'ciclo_id', 'curso_academico']);
            $table->index(['ciclo_id', 'curso_academico']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_tutor');
    }
};
