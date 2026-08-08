<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable a nivel de BD porque no hay alumnos reales que proteger todavia
     * (confirmado con Fernando). La obligatoriedad se aplica a partir de ahora
     * en StoreAlumnoRequest/UpdateAlumnoRequest e ImportAlumnosService, no en
     * el esquema -- así no rompe filas de desarrollo ya existentes sin grupo.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->after('ciclo_id')
                ->constrained('grupos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_id');
        });
    }
};
