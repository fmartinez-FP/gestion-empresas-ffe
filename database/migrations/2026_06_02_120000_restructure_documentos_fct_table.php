<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vaciar la tabla (no hay datos de producción en dev)
        DB::table('documentos_fct')->truncate();

        // 2. Eliminar FK existente sobre asignacion_id (sin CASCADE)
        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->dropForeign(['asignacion_id']);
        });

        // 3. Reestructurar columnas
        Schema::table('documentos_fct', function (Blueprint $table) {
            // Eliminar columnas del esquema antiguo
            $table->dropColumn(['archivo_path', 'firmado', 'firmado_at', 'archivo_firmado_path']);
        });

        // 4. Modificar ENUM tipo (MySQL requiere redefinicion completa)
        DB::statement("ALTER TABLE documentos_fct MODIFY COLUMN tipo ENUM('plan_formativo','ficha_seguimiento','informe_final','firmado') NOT NULL");

        // 5. Añadir columnas nuevas y restaurar FK con CASCADE
        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->string('nombre_archivo', 255)->after('tipo');
            $table->string('ruta_disco', 500)->after('nombre_archivo');
            $table->string('disco', 50)->default('private')->after('ruta_disco');
            $table->timestamp('subido_at')->nullable()->after('generado_at');

            // FK con ON DELETE CASCADE
            $table->foreign('asignacion_id')
                  ->references('id')
                  ->on('asignaciones_fct')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        DB::table('documentos_fct')->truncate();

        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->dropForeign(['asignacion_id']);
            $table->dropColumn(['nombre_archivo', 'ruta_disco', 'disco', 'subido_at']);
        });

        DB::statement("ALTER TABLE documentos_fct MODIFY COLUMN tipo ENUM('plan_formativo','ficha_seguimiento','informe_final') NOT NULL");

        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->string('archivo_path', 500)->nullable();
            $table->boolean('firmado')->default(false);
            $table->timestamp('firmado_at')->nullable();
            $table->string('archivo_firmado_path', 500)->nullable();
            $table->foreign('asignacion_id')->references('id')->on('asignaciones_fct');
        });
    }
};
