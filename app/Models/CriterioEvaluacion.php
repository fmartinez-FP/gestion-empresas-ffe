<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriterioEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'criterios_evaluacion';

    protected $fillable = [
        'resultado_aprendizaje_id',
        'codigo',
        'descripcion',
    ];

    public function resultadoAprendizaje(): BelongsTo
    {
        return $this->belongsTo(ResultadoAprendizaje::class, 'resultado_aprendizaje_id');
    }

    public function asignaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            AsignacionFct::class,
            'asignacion_ce',
            'criterio_evaluacion_id',
            'asignacion_id'
        )->withTimestamps();
    }

    public function elegibles(): HasMany
    {
        return $this->hasMany(ElegibleFfeCe::class, 'criterio_evaluacion_id');
    }
}
