<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_via', 50)->nullable();
            $table->string('nombre_via');
            $table->string('numero', 20)->nullable();
            $table->string('codigo_postal', 5)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->index('empresa_id');
            $table->index(['latitud', 'longitud']);
        });

        // Migrar datos existentes
        DB::statement("
            INSERT INTO direcciones (empresa_id, nombre_via, principal, created_at, updated_at)
            SELECT id, direccion, 1, NOW(), NOW()
            FROM empresas
            WHERE direccion IS NOT NULL AND TRIM(direccion) != ''
        ");

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('direccion')->nullable();
        });
        Schema::dropIfExists('direcciones');
    }
};
