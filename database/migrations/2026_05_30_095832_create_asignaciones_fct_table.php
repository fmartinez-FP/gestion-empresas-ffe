<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_fct', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sede_id')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->foreignId('tutor_empresa_id')->nullable()->constrained('personas_contacto')->nullOnDelete();
            $table->foreignId('tutor_ies_id')->constrained('users');
            $table->foreignId('ciclo_id')->constrained('ciclos_formativos');
            $table->string('curso_academico', 9);
            $table->tinyInteger('numero_curso');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->integer('num_horas')->nullable();
            $table->text('horario')->nullable();
            $table->text('calendario')->nullable();
            $table->enum('estado', ['activa', 'finalizada', 'cancelada'])->default('activa');
            $table->text('motivo_baja')->nullable();
            $table->tinyInteger('intervalo_email_tutor')->default(14);
            $table->timestamp('ultimo_email_tutor_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_fct');
    }
};
