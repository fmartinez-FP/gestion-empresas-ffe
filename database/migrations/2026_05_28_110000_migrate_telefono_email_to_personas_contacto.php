<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Actualizar personas principales con telefono/email de la empresa
        // (todas tienen null actualmente, así que no hay riesgo de sobreescribir)
        DB::statement("
            UPDATE personas_contacto pc
            INNER JOIN empresas e ON pc.empresa_id = e.id AND pc.principal = 1
            SET
                pc.telefono = NULLIF(TRIM(COALESCE(e.telefono, '')), ''),
                pc.email    = NULLIF(TRIM(COALESCE(e.email, '')), '')
            WHERE e.telefono IS NOT NULL OR e.email IS NOT NULL
        ");

        // Paso 2: Crear 'Contacto general' para empresas con telefono/email pero sin persona
        DB::statement("
            INSERT INTO personas_contacto (empresa_id, nombre, telefono, email, principal, created_at, updated_at)
            SELECT e.id,
                   'Contacto general',
                   NULLIF(TRIM(COALESCE(e.telefono, '')), ''),
                   NULLIF(TRIM(COALESCE(e.email, '')), ''),
                   1, NOW(), NOW()
            FROM empresas e
            LEFT JOIN personas_contacto pc ON pc.empresa_id = e.id
            WHERE pc.id IS NULL
              AND (
                (e.telefono IS NOT NULL AND TRIM(e.telefono) != '')
                OR (e.email IS NOT NULL AND TRIM(e.email) != '')
              )
        ");

        // Paso 3: Eliminar columnas de empresas
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('telefono', 20)->nullable()->after('cif');
            $table->string('email', 255)->nullable()->after('telefono');
        });

        // Restaurar desde persona principal
        DB::statement("
            UPDATE empresas e
            INNER JOIN personas_contacto pc ON pc.empresa_id = e.id AND pc.principal = 1
            SET e.telefono = pc.telefono, e.email = pc.email
        ");
    }
};
