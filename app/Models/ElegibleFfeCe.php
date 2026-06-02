<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElegibleFfeCe extends Model
{
    protected $table = 'elegibles_ffe_ce';

    protected $fillable = [
        'criterio_evaluacion_id',
        'curso_academico',
        'created_by_id',
    ];

    public function criterioEvaluacion(): BelongsTo
    {
        return $this->belongsTo(CriterioEvaluacion::class, 'criterio_evaluacion_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
