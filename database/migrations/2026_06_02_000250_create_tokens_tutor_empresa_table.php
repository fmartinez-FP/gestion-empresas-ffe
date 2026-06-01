<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens_tutor_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones_fct')->onDelete('cascade');
            $table->string('token', 100);
            $table->timestamp('expires_at');
            $table->timestamp('usado_at')->nullable();
            $table->string('ip_uso', 45)->nullable();
            $table->timestamps();
            $table->unique('token', 'uq_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_tutor_empresa');
    }
};
