<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ResultadoAprendizaje extends Model
{
    use HasFactory;

    protected $table = 'resultados_aprendizaje';

    protected $fillable = [
        'modulo_id',
        'codigo',
        'descripcion',
        'activo_ffe',
        'curso_academico_activo',
    ];

    protected $casts = [
        'activo_ffe' => 'boolean',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloProfesional::class, 'modulo_id');
    }

    public function criterios(): HasMany
    {
        return $this->hasMany(CriterioEvaluacion::class, 'resultado_aprendizaje_id');
    }

    public function asignaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            AsignacionFct::class,
            'asignacion_ra',
            'resultado_aprendizaje_id',
            'asignacion_id'
        )->withTimestamps();
    }

    public function elegibles(): HasMany
    {
        return $this->hasMany(ElegibleFfe::class, 'resultado_aprendizaje_id');
    }

    public function scopeElegiblesParaCurso(Builder $query, string $cursoAcademico): Builder
    {
        return $query->whereHas('elegibles', function (Builder $q) use ($cursoAcademico) {
            $q->where('curso_academico', $cursoAcademico);
        });
    }

    public function esElegibleParaCurso(string $cursoAcademico): bool
    {
        return $this->elegibles()->where('curso_academico', $cursoAcademico)->exists();
    }
}
