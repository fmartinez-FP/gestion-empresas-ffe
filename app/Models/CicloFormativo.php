<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloFormativo extends Model
{
    use HasFactory;

    protected $table = 'ciclos_formativos';

    protected $fillable = [
        'codigo',
        'nombre',
        'nivel',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Empresas que aceptan alumnos de este ciclo
     */
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_ciclo', 'ciclo_id', 'empresa_id')
            ->withPivot(['acepta_primero', 'acepta_segundo'])
            ->withTimestamps();
    }

    /**
     * Usuarios responsables de este ciclo (legacy - relación simple)
     */
    public function responsables(): HasMany
    {
        return $this->hasMany(User::class, 'ciclo_id')
            ->where('rol', 'responsable_ciclo');
    }

    /**
     * Usuarios asignados a este ciclo (nueva relación muchos a muchos)
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ciclo_user', 'ciclo_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Colocaciones de este ciclo
     */
    public function colocaciones(): HasMany
    {
        return $this->hasMany(Colocacion::class, 'ciclo_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope para ciclos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por nivel
     */
    public function scopeNivel($query, string $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Nombre del nivel en formato legible
     */
    public function getNombreNivelAttribute(): string
    {
        return match($this->nivel) {
            'basica' => 'FP Básica',
            'media' => 'Grado Medio',
            'superior' => 'Grado Superior',
            default => $this->nivel,
        };
    }

    /**
     * Nombre completo con nivel
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} ({$this->nombre_nivel})";
    }

    /**
     * Etiqueta corta para listados
     */
    public function getEtiquetaAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }
}
