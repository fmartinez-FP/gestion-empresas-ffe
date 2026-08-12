<?php

namespace App\Services;

use App\Models\NoLectivoIes;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Gestiona los dias no lectivos del centro (festivos/no lectivos IES), compartidos
 * por todos los calendarios FFE de los alumnos. Sin curso_academico (decision
 * explicita 2026-08-12): solo interesa el curso activo, la informacion historica
 * no se conserva con ese proposito.
 */
class NoLectivoIesService
{
    /**
     * Marca como no lectivo cada dia laborable (L-V) del rango [inicio, fin] con el
     * mismo motivo. Upsert por fecha (updateOrCreate), igual que
     * CalendarioAsignacionService::marcarDia().
     *
     * @return Collection<NoLectivoIes>
     */
    public function marcarRango(Carbon $inicio, Carbon $fin, ?string $motivo = null): Collection
    {
        $creados = collect();

        foreach (CarbonPeriod::create($inicio, $fin) as $fecha) {
            if ($fecha->isWeekend()) {
                continue;
            }

            $creados->push(
                NoLectivoIes::updateOrCreate(
                    ['fecha' => $fecha->toDateString()],
                    ['motivo' => $motivo]
                )
            );
        }

        return $creados;
    }

    public function eliminar(NoLectivoIes $dia): void
    {
        $dia->delete();
    }

    /**
     * Fechas ('Y-m-d') marcadas como no lectivas de centro, opcionalmente acotadas
     * a un rango [desde, hasta] inclusive.
     */
    public function fechasExcluidas(?Carbon $desde = null, ?Carbon $hasta = null): Collection
    {
        $query = NoLectivoIes::query();

        if ($desde !== null && $hasta !== null) {
            $query->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);
        }

        return $query->pluck('fecha')
            ->map(fn ($f) => Carbon::parse($f)->toDateString());
    }
}
