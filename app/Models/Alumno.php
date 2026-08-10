<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre',
        'apellidos',
        'email',
        'telefono',
        'grupo_id',
        'curso_academico',
        'importado_via',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionFct::class, 'alumno_id');
    }

    public function asignacionActiva()
    {
        return $this->hasOne(AsignacionFct::class, 'alumno_id')
            ->where('estado', 'activa')
            ->whereNull('deleted_at')
            ->latest();
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellidos}, {$this->nombre}";
    }

    /**
     * ciclo_id/numero_curso ya no son columnas propias (eliminadas en
     * 2026_08_10_120000_remove_ciclo_and_curso_columns_from_alumnos): se
     * derivan siempre de grupo_id -> grupos, para eliminar la divergencia
     * de datos confirmada en sesion 2026-08-10.
     *
     * IMPORTANTE: no es eager-cargable como 'ciclo' -- usar 'grupo.ciclo'
     * en ->with()/->load().
     */
    public function getCicloAttribute(): ?CicloFormativo
    {
        return $this->grupo?->ciclo;
    }

    public function getCicloIdAttribute(): ?int
    {
        return $this->grupo?->ciclo_id;
    }

    public function getNumeroCursoAttribute(): ?int
    {
        return $this->grupo?->numero_curso;
    }
}
