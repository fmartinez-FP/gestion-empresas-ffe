<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colocaciones', function (Blueprint $table) {
            $table->enum('origen', ['manual', 'automatica'])->default('manual')->after('num_horas');
        });

        Schema::create('colocacion_asignacion_fct', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colocacion_id')->constrained('colocaciones')->cascadeOnDelete();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('asignacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colocacion_asignacion_fct');
        Schema::table('colocaciones', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
