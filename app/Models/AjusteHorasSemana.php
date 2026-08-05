<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteHorasSemana extends Model
{
    use HasFactory;

    protected $table = 'ajuste_horas_semana';

    protected $fillable = [
        'asignacion_id',
        'semana',
        'ajuste',
        'motivo',
        'created_by_id',
    ];

    protected $casts = [
        'semana' => 'date',
        'ajuste' => 'decimal:2',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
