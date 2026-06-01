<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoDiario extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_diario';

    protected $fillable = [
        'asignacion_id',
        'fecha',
        'descripcion_tareas',
        'evidencia_path',
        'hora_entrada',
        'hora_salida',
        'confirmado_tutor',
        'confirmado_at',
        'comentario_tutor',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'confirmado_tutor' => 'boolean',
        'confirmado_at'    => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }
}
