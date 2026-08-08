<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sustituye ciclo_id por grupo_id en profesor_tutor: un profesor
     * tutoriza un grupo concreto (1ºA, 1ºB), no un ciclo entero. Se
     * altera en vez de recrear porque la tabla es de esta misma sesion
     * y no tiene datos reales que preservar (confirmado con Fernando).
     * curso_academico se mantiene en el pivote para seguir versionando
     * la asignacion anual (decision cerrada en la sesion 2026-08-07).
     *
     * NOTA: el unique(user_id, ciclo_id, curso_academico) original es el
     * unico indice cuyo prefijo empieza por user_id, y por tanto es el
     * que satisface el FK de user_id (no solo el de ciclo_id). No se
     * puede borrar directamente sin romper ese FK -- se crea un indice
     * temporal solo sobre user_id como puente, y se retira al final una
     * vez existe el nuevo unique(user_id, grupo_id, curso_academico).
     */
    public function up(): void
    {
        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropForeign(['ciclo_id']);
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->index('user_id', 'profesor_tutor_user_id_temp_index');
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'ciclo_id', 'curso_academico']);
            $table->dropIndex(['ciclo_id', 'curso_academico']);
            $table->dropColumn('ciclo_id');
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->foreignId('grupo_id')->after('user_id')->constrained('grupos')->onDelete('cascade');
            $table->unique(['user_id', 'grupo_id', 'curso_academico']);
            $table->index(['grupo_id', 'curso_academico']);
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropIndex('profesor_tutor_user_id_temp_index');
        });
    }

    public function down(): void
    {
        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->index('user_id', 'profesor_tutor_user_id_temp_index');
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'grupo_id', 'curso_academico']);
            $table->dropIndex(['grupo_id', 'curso_academico']);
            $table->dropColumn('grupo_id');
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->foreignId('ciclo_id')->after('user_id')->constrained('ciclos_formativos')->onDelete('cascade');
            $table->unique(['user_id', 'ciclo_id', 'curso_academico']);
            $table->index(['ciclo_id', 'curso_academico']);
        });

        Schema::table('profesor_tutor', function (Blueprint $table) {
            $table->dropIndex('profesor_tutor_user_id_temp_index');
        });
    }
};
