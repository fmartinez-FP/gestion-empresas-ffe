<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir índices para optimizar consultas frecuentes
     */
    public function up(): void
    {
        // Índices para empresas
        Schema::table('empresas', function (Blueprint $table) {
            // Índice compuesto para búsquedas por nombre
            $table->index('nombre', 'idx_empresas_nombre');
            
            // Índice para filtrar por estado del convenio (fecha_firma)
            $table->index('fecha_firma', 'idx_empresas_fecha_firma');
            
            // Índice para el creador
            $table->index('creador_id', 'idx_empresas_creador');
        });

        // Índices para colocaciones
        Schema::table('colocaciones', function (Blueprint $table) {
            // Índice compuesto para filtros frecuentes
            $table->index(['curso_academico', 'ciclo_id'], 'idx_colocaciones_curso_ciclo');
            
            // Índice para empresa
            $table->index('empresa_id', 'idx_colocaciones_empresa');
            
            // Índice para ordenación por fecha
            $table->index('created_at', 'idx_colocaciones_created');
        });

        // Índices para usuarios
        Schema::table('users', function (Blueprint $table) {
            // Índice para filtrar por rol
            $table->index('rol', 'idx_users_rol');
            
            // Índice para filtrar activos
            $table->index('activo', 'idx_users_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex('idx_empresas_nombre');
            $table->dropIndex('idx_empresas_fecha_firma');
            $table->dropIndex('idx_empresas_creador');
        });

        Schema::table('colocaciones', function (Blueprint $table) {
            $table->dropIndex('idx_colocaciones_curso_ciclo');
            $table->dropIndex('idx_colocaciones_empresa');
            $table->dropIndex('idx_colocaciones_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_rol');
            $table->dropIndex('idx_users_activo');
        });
    }
};
