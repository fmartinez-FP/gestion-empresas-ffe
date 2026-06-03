<?php

namespace App\Services;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CalendarioAsignacionService
{
    /**
     * Marca un dia en el calendario de la asignacion.
     * Si ya existe una entrada para esa fecha, la actualiza.
     */
    public function marcarDia(
        AsignacionFct $asignacion,
        Carbon $fecha,
        string $tipo,
        ?string $motivo = null
    ): CalendarioAsignacion {
        return CalendarioAsignacion::updateOrCreate(
            [
                "asignacion_id" => $asignacion->id,
                "fecha"         => $fecha->toDateString(),
            ],
            [
                "tipo"   => $tipo,
                "motivo" => $motivo,
            ]
        );
    }

    /**
     * Elimina una entrada del calendario.
     */
    public function eliminarDia(CalendarioAsignacion $dia): void
    {
        $dia->delete();
    }

    /**
     * Calcula los dias laborables de una asignacion:
     * - Entre fecha_inicio y fecha_fin (inclusive)
     * - Excluyendo sabados y domingos
     * - Excluyendo dias con tipo festivo, no_lectivo o baja en calendario_asignacion
     */
    public function diasLaborables(AsignacionFct $asignacion): int
    {
        if ($asignacion->fecha_inicio === null || $asignacion->fecha_fin === null) {
            return 0;
        }

        $excluidos = CalendarioAsignacion::where("asignacion_id", $asignacion->id)
            ->whereIn("tipo", ["festivo", "no_lectivo", "baja"])
            ->pluck("fecha")
            ->map(fn ($f) => Carbon::parse($f)->toDateString())
            ->flip()
            ->all();

        $periodo = CarbonPeriod::create(
            $asignacion->fecha_inicio,
            $asignacion->fecha_fin
        );

        $count = 0;
        foreach ($periodo as $dia) {
            if ($dia->isWeekend()) {
                continue;
            }
            if (isset($excluidos[$dia->toDateString()])) {
                continue;
            }
            $count++;
        }

        return $count;
    }
}
