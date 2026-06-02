<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin','responsable_ffe','responsable_ciclo','profesor','alumno','tutor_empresa') NOT NULL DEFAULT 'profesor'");

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password_change_required')->default(false)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_change_required');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin','responsable_ffe','responsable_ciclo','profesor') NOT NULL DEFAULT 'profesor'");
    }
};
