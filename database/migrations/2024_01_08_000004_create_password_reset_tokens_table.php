<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para tokens de recuperación de contraseña.
     * Nota: ya creada por 0001_01_01_000000_create_users_table.
     * Se mantiene por compatibilidad con el registro en migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // No se dropea: la gestiona 0001_01_01_000000_create_users_table
    }
};
