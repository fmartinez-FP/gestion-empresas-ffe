<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('no_lectivos_ies', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('motivo', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('no_lectivos_ies');
    }
};
