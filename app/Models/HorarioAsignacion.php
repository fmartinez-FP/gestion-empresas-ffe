<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioAsignacion extends Model
{
    use HasFactory;

    protected $table = 'horario_asignacion';

    protected $fillable = [
        'asignacion_id',
        'dia',
        'entrada_manana',
        'salida_manana',
        'entrada_tarde',
        'salida_tarde',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }

    public function esJornadaPartida(): bool
    {
        return $this->entrada_tarde !== null && $this->salida_tarde !== null;
    }
}
