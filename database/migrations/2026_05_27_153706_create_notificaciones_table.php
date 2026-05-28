<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->string('titulo');
            $table->string('url', 500)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['modelo', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
