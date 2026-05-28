<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->string('curso_academico', 9)->nullable()->after('empresa_id');
        });

        // Asignar curso activo a valoraciones existentes
        $cursoActivo = \App\Models\Configuracion::cursoActivo();
        DB::table('valoraciones')->whereNull('curso_academico')->update(['curso_academico' => $cursoActivo]);

        Schema::table('valoraciones', function (Blueprint $table) {
            $table->unique(['empresa_id', 'curso_academico'], 'valoraciones_empresa_curso_unique');
        });
    }

    public function down(): void
    {
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->dropUnique('valoraciones_empresa_curso_unique');
            $table->dropColumn('curso_academico');
        });
    }
};
