<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina ciclo_id y numero_curso como columnas propias de alumnos.
     * A partir de ahora se derivan siempre de grupo_id -> grupos.ciclo_id /
     * grupos.numero_curso via accessors en el modelo Alumno, para eliminar
     * la divergencia de datos confirmada en sesion 2026-08-10 (25/25
     * alumnos sembrados con desajuste entre columnas propias y grupo real).
     *
     * grupo_id se mantiene nullable a nivel de BD (decision Fernando,
     * sesion 2026-08-10): la obligatoriedad se sigue aplicando solo en
     * StoreAlumnoRequest/UpdateAlumnoRequest/ImportAlumnosService, no en
     * el esquema.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['ciclo_id']);
            $table->dropColumn(['ciclo_id', 'numero_curso']);
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('ciclo_id')->nullable()->after('telefono')->constrained('ciclos_formativos');
            $table->tinyInteger('numero_curso')->default(2)->after('ciclo_id');
        });
    }
};
