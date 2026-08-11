<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE calendario_asignacion MODIFY tipo ENUM('laborable', 'festivo', 'no_lectivo', 'baja', 'ausencia_no_justificada') NOT NULL DEFAULT 'laborable'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE calendario_asignacion MODIFY tipo ENUM('laborable', 'festivo', 'no_lectivo', 'baja') NOT NULL DEFAULT 'laborable'");
    }
};
