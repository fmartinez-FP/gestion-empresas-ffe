<?php

namespace App\Services;

use App\Models\AsignacionFct;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Construye el calendario de días laborables (Lunes-Viernes) de una asignación FCT,
 * los agrupa por semana ISO (lunes de esa semana) y resuelve el estado de cada día
 * cruzando seguimiento_diario y calendario_asignacion.
 *
 * NOTA: fijo Lunes-Viernes por decisión explícita (2026-08-11) — si un alumno trabaja
 * fin de semana es un caso esporádico, se abordará en una versión posterior si hace falta.
 */
class CuadernoCalendarioService
{
    /**
     * Días laborables (L-V) dentro de fecha_inicio-fecha_fin de la asignación.
     * No depende del horario configurado (decisión explícita: siempre L-V).
     */
    public function diasLaborables(AsignacionFct $asignacion): Collection
    {
        if ($asignacion->fecha_inicio === null || $asignacion->fecha_fin === null) {
            return collect();
        }

        $periodo = CarbonPeriod::create($asignacion->fecha_inicio, $asignacion->fecha_fin);

        return collect($periodo)
            ->filter(fn (Carbon $fecha) => ! $fecha->isWeekend())
            ->values();
    }

    /**
     * Agrupa una colección de fechas por el lunes de su semana ISO.
     * Devuelve Collection<string lunes => Collection<Carbon>>, ordenada cronológicamente.
     * Las semanas de los extremos del período pueden tener menos de 5 días si
     * fecha_inicio/fecha_fin no caen en lunes/viernes.
     */
    public function agruparPorSemana(Collection $dias): Collection
    {
        return $dias
            ->groupBy(fn (Carbon $fecha) => $fecha->copy()->startOfWeek(Carbon::MONDAY)->toDateString())
            ->sortKeys();
    }

    /**
     * Resuelve el estado de un día concreto.
     *
     * @param Collection $seguimientosPorFecha SeguimientoDiario indexado por 'Y-m-d'
     * @param Collection $festivosPorFecha      CalendarioAsignacion (tipo != laborable) indexado por 'Y-m-d'
     * @return array{estado: string, motivo: ?string, tipo: ?string, seguimiento: mixed}
     *   estado: 'confirmado' | 'pendiente' | 'no_trabajado' | 'festivo'
     */
    public function estadoDia(Carbon $fecha, Collection $seguimientosPorFecha, Collection $festivosPorFecha): array
    {
        $clave = $fecha->toDateString();

        if ($festivosPorFecha->has($clave)) {
            $festivo = $festivosPorFecha->get($clave);

            return [
                'estado'      => $festivo->tipo === 'ausencia_no_justificada' ? 'ausencia' : 'festivo',
                'motivo'      => $festivo->motivo,
                'tipo'        => $festivo->tipo,
                'seguimiento' => null,
            ];
        }

        $seguimiento = $seguimientosPorFecha->get($clave);

        if ($seguimiento !== null) {
            return [
                'estado'      => $seguimiento->confirmado_tutor ? 'confirmado' : 'pendiente',
                'motivo'      => null,
                'tipo'        => null,
                'seguimiento' => $seguimiento,
            ];
        }

        // Sin entrada: si es un día ya pasado, no se trabajó (rojo).
        // Si es hoy o un día futuro, aún puede registrarse (amarillo/neutro).
        if ($fecha->lt(Carbon::today())) {
            return ['estado' => 'no_trabajado', 'motivo' => null, 'tipo' => null, 'seguimiento' => null];
        }

        return ['estado' => 'pendiente', 'motivo' => null, 'tipo' => null, 'seguimiento' => null];
    }

    /**
     * Resuelve el estado de una coleccion de fechas de una sola vez (evita N+1):
     * una unica query de seguimientos y una de festivos para todo el rango pedido.
     * Devuelve Collection<string 'Y-m-d' => array{estado,motivo,tipo,seguimiento}>.
     */
    public function resolverEstados(AsignacionFct $asignacion, Collection $fechas): Collection
    {
        $fechasStr = $fechas->map(fn (Carbon $f) => $f->toDateString());

        $seguimientosPorFecha = $asignacion->seguimientos()
            ->whereIn('fecha', $fechasStr)
            ->get()
            ->keyBy(fn ($s) => Carbon::parse($s->fecha)->toDateString());

        $festivosPorFecha = $asignacion->calendario()
            ->whereIn('fecha', $fechasStr)
            ->where('tipo', '!=', 'laborable')
            ->get()
            ->keyBy(fn ($c) => Carbon::parse($c->fecha)->toDateString());

        return $fechas->mapWithKeys(fn (Carbon $fecha) => [
            $fecha->toDateString() => $this->estadoDia($fecha, $seguimientosPorFecha, $festivosPorFecha),
        ]);
    }

    /**
     * Dias laborables (L-V) de un mes concreto, acotados al periodo real de la asignacion
     * (fecha_inicio-fecha_fin). Fuera de ese rango no hay nada que mostrar en el overview.
     */
    public function diasDelMes(AsignacionFct $asignacion, Carbon $mes): Collection
    {
        if ($asignacion->fecha_inicio === null || $asignacion->fecha_fin === null) {
            return collect();
        }

        $inicioMes = $mes->copy()->startOfMonth();
        $finMes    = $mes->copy()->endOfMonth();

        return collect(CarbonPeriod::create($inicioMes, $finMes))
            ->filter(fn (Carbon $fecha) => ! $fecha->isWeekend()
                && $fecha->between($asignacion->fecha_inicio, $asignacion->fecha_fin))
            ->values();
    }

    /**
     * Mes por defecto a mostrar: el explicito por query, o el mes de hoy si cae dentro
     * del periodo de la asignacion, o el primer mes del periodo en caso contrario.
     */
    public function resolverMesOverview(AsignacionFct $asignacion, ?string $mesQuery): Carbon
    {
        if ($mesQuery !== null) {
            try {
                return Carbon::createFromFormat('Y-m', $mesQuery)->startOfMonth();
            } catch (\Exception $e) {
                // formato invalido, cae al calculo por defecto
            }
        }

        $hoy = Carbon::today();

        if ($asignacion->fecha_inicio !== null && $asignacion->fecha_fin !== null
            && $hoy->between($asignacion->fecha_inicio, $asignacion->fecha_fin)) {
            return $hoy->copy()->startOfMonth();
        }

        return $asignacion->fecha_inicio !== null
            ? Carbon::parse($asignacion->fecha_inicio)->startOfMonth()
            : $hoy->copy()->startOfMonth();
    }
}
