<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
