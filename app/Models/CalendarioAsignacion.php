<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioAsignacion extends Model
{
    use HasFactory;

    protected $table = 'calendario_asignacion';

    protected $fillable = [
        'asignacion_id',
        'fecha',
        'tipo',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }

    public function esLaborable(): bool
    {
        return $this->tipo === 'laborable';
    }
}
