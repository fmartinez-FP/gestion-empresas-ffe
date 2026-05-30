<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre', 100);
            $table->string('apellidos', 150);
            $table->string('email', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos');
            $table->string('curso_academico', 9);
            $table->tinyInteger('numero_curso')->default(2);
            $table->enum('importado_via', ['manual', 'excel'])->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
