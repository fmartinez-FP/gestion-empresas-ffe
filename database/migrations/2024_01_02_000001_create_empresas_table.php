<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('cif', 15)->unique();
            $table->string('direccion', 300)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('persona_contacto', 100)->nullable();
            $table->string('num_convenio', 50)->nullable();
            $table->date('fecha_firma')->nullable();
            $table->foreignId('creador_id')->constrained('users')->cascadeOnUpdate();
            $table->text('notas')->nullable();
            $table->timestamps();
            
            $table->index('nombre');
            $table->index('fecha_firma');
            $table->index('creador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
