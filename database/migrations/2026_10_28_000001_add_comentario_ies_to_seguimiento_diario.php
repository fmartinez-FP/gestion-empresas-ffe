<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * comentario_tutor lo escribían tanto el tutor de empresa (enlace externo) como el
     * tutor IES al confirmar (que además lo ponía a NULL si no enviaba texto), de modo que
     * se pisaban. A partir de ahora comentario_tutor es solo del tutor de empresa y el
     * comentario de la confirmación del tutor IES va en comentario_ies.
     */
    public function up(): void
    {
        Schema::table('seguimiento_diario', function (Blueprint $table) {
            $table->string('comentario_ies', 500)->nullable()->after('comentario_tutor');
        });
    }

    public function down(): void
    {
        Schema::table('seguimiento_diario', function (Blueprint $table) {
            $table->dropColumn('comentario_ies');
        });
    }
};
