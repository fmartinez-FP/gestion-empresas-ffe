<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFormativoDatos extends Model
{
    protected $table = 'plan_formativo_datos';

    protected $fillable = [
        'asignacion_id',
        'medidas_discapacidad',
        'medidas_discapacidad_detalle',
        'autorizacion_extraordinaria',
        'autorizacion_extraordinaria_detalle',
        'intervalo',
        'periodos',
        'observaciones',
        'formaciones_especificas',
        'imparticion_modulos',
        'purgar_after',
    ];

    protected $casts = [
        'medidas_discapacidad'       => 'boolean',
        'autorizacion_extraordinaria' => 'boolean',
        'imparticion_modulos'        => 'array',
        'purgar_after'               => 'date',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }

    /** Intervalos disponibles con etiqueta legible */
    public static function intervalos(): array
    {
        return [
            'diario'          => 'Diario',
            'semanal'         => 'Semanal',
            'mensual'         => 'Mensual',
            'otros'           => 'Otros',
            'varias_empresas' => 'Varias empresas',
        ];
    }

    /** Comprueba si los datos siguen vigentes (no purgables aún) */
    public function estaVigente(): bool
    {
        return $this->purgar_after->isFuture() || $this->purgar_after->isToday();
    }
}
