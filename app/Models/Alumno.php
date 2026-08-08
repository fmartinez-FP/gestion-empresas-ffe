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
        'ciclo_id',
        'grupo_id',
        'curso_academico',
        'numero_curso',
        'importado_via',
    ];

    protected $casts = [
        'numero_curso' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
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
}
