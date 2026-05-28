<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('registrado_por_id')->constrained('users')->onDelete('cascade');
            
            // Tipo de contacto
            $table->enum('tipo', ['llamada', 'email', 'visita', 'reunion_online', 'otro'])->default('llamada');
            
            // Resultado
            $table->enum('resultado', ['exitoso', 'sin_respuesta', 'pendiente', 'cita_programada'])->default('exitoso');
            
            // Detalles
            $table->string('persona_contacto')->nullable();
            $table->text('notas')->nullable();
            $table->datetime('fecha_contacto');
            $table->datetime('fecha_seguimiento')->nullable();
            
            // Archivo adjunto
            $table->string('archivo_adjunto')->nullable();
            $table->string('archivo_nombre')->nullable();
            
            $table->timestamps();
            
            $table->index(['empresa_id', 'fecha_contacto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
