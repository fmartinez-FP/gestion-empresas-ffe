<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->date('purgar_after')->nullable()->after('archivo_firmado_path');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_fct', function (Blueprint $table) {
            $table->dropColumn('purgar_after');
        });
    }
};
