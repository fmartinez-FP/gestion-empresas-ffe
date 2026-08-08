<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = [
        'ciclo_id',
        'numero_curso',
        'etiqueta',
        'activo',
    ];

    protected $casts = [
        'numero_curso' => 'integer',
        'activo'       => 'boolean',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'grupo_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * "1º A" si tiene etiqueta, "1º" si el ciclo no se divide en grupos paralelos.
     */
    public function getEtiquetaCompletaAttribute(): string
    {
        $numero = "{$this->numero_curso}º";
        return $this->etiqueta !== '' ? "{$numero} {$this->etiqueta}" : $numero;
    }

    /**
     * "DAM - 1º A", útil para selects/checkboxes donde hace falta contexto del ciclo.
     */
    public function getEtiquetaConCicloAttribute(): string
    {
        $codigo = $this->ciclo?->codigo ?? '?';
        return "{$codigo} — {$this->etiqueta_completa}";
    }
}
