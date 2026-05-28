<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('cargo', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        // Migrar datos existentes
        DB::statement("
            INSERT INTO personas_contacto (empresa_id, nombre, principal, created_at, updated_at)
            SELECT id, persona_contacto, 1, NOW(), NOW()
            FROM empresas
            WHERE persona_contacto IS NOT NULL AND TRIM(persona_contacto) != ''
        ");

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('persona_contacto');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('persona_contacto', 100)->nullable()->after('email');
        });

        DB::statement("
            UPDATE empresas e
            INNER JOIN personas_contacto pc ON pc.empresa_id = e.id AND pc.principal = 1
            SET e.persona_contacto = pc.nombre
        ");

        Schema::dropIfExists('personas_contacto');
    }
};
