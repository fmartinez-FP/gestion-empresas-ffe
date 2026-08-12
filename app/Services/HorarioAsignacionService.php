<?php

namespace App\Services;

use App\Models\AsignacionFct;
use App\Models\HorarioAsignacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\ValidationException;

class HorarioAsignacionService
{
    private const DIAS_ORDEN = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    private const DIAS_LABEL = [
        'lunes'     => 'Lunes',
        'martes'    => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves'    => 'Jueves',
        'viernes'   => 'Viernes',
        'sabado'    => 'Sábado',
        'domingo'   => 'Domingo',
    ];

    private const MAX_HORAS_DIA = 8.0;
    private const MAX_HORAS_SEMANA = 40.0;

    public function __construct(private NoLectivoIesService $noLectivoIesService)
    {
    }

    public function horasDiarias(HorarioAsignacion $horario): float
    {
        $horas = $this->diferenciaHoras($horario->entrada_manana, $horario->salida_manana);

        if ($horario->entrada_tarde !== null && $horario->salida_tarde !== null) {
            $horas += $this->diferenciaHoras($horario->entrada_tarde, $horario->salida_tarde);
        }

        return round($horas, 2);
    }

    public function horasPrevistas(AsignacionFct $asignacion): float
    {
        if ($asignacion->fecha_inicio === null || $asignacion->fecha_fin === null) {
            throw new \InvalidArgumentException(
                'No se pueden calcular las horas previstas: faltan fecha de inicio y/o fecha de fin en la asignación.'
            );
        }

        $horariosPorDia = $asignacion->horarios->keyBy('dia');

        if ($horariosPorDia->isEmpty()) {
            return 0.0;
        }

        $fechasExcluidas = $asignacion->calendario()
            ->where('tipo', '!=', 'laborable')
            ->pluck('fecha')
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->flip()
            ->union(
                $this->noLectivoIesService
                    ->fechasExcluidas($asignacion->fecha_inicio, $asignacion->fecha_fin)
                    ->flip()
            );

        $total   = 0.0;
        $periodo = CarbonPeriod::create($asignacion->fecha_inicio, $asignacion->fecha_fin);

        foreach ($periodo as $fecha) {
            if (isset($fechasExcluidas[$fecha->toDateString()])) {
                continue;
            }

            $diaSemana = self::DIAS_ORDEN[$fecha->dayOfWeekIso - 1];
            $horario   = $horariosPorDia->get($diaSemana);

            if ($horario === null) {
                continue;
            }

            $total += $this->horasDiarias($horario);
        }

        return round($total, 2);
    }

    public function horasRealizadas(AsignacionFct $asignacion): float
    {
        $horariosPorDia = $asignacion->horarios->keyBy('dia');

        $totalConfirmadas = $asignacion->seguimientos()
            ->where('confirmado_tutor', true)
            ->get()
            ->sum(function ($seguimiento) use ($horariosPorDia) {
                $diaSemana = self::DIAS_ORDEN[Carbon::parse($seguimiento->fecha)->dayOfWeekIso - 1];
                $horario   = $horariosPorDia->get($diaSemana);

                return $horario ? $this->horasDiarias($horario) : 0.0;
            });

        $totalAjustes = (float) $asignacion->ajustesHoras()->sum('ajuste');

        return round($totalConfirmadas + $totalAjustes, 2);
    }

    /**
     * Previstas/realizadas/ajuste de una semana concreta (lunes a viernes a partir de $lunesSemana).
     * Mismo criterio de exclusion de festivos/no_lectivo/baja que horasPrevistas(), acotado a la semana.
     *
     * @return array{previstas: float, confirmadas: float, ajuste: float, realizadas: float}
     */
    public function horasSemana(AsignacionFct $asignacion, Carbon $lunesSemana): array
    {
        $horariosPorDia = $asignacion->horarios->keyBy('dia');
        $viernesSemana  = $lunesSemana->copy()->addDays(4);

        $fechasExcluidas = $asignacion->calendario()
            ->whereBetween('fecha', [$lunesSemana->toDateString(), $viernesSemana->toDateString()])
            ->where('tipo', '!=', 'laborable')
            ->pluck('fecha')
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->flip()
            ->union(
                $this->noLectivoIesService
                    ->fechasExcluidas($lunesSemana, $viernesSemana)
                    ->flip()
            );

        $seguimientosPorFecha = $asignacion->seguimientos()
            ->whereBetween('fecha', [$lunesSemana->toDateString(), $viernesSemana->toDateString()])
            ->get()
            ->keyBy(fn ($s) => Carbon::parse($s->fecha)->toDateString());

        $previstas   = 0.0;
        $confirmadas = 0.0;

        foreach (CarbonPeriod::create($lunesSemana, $viernesSemana) as $fecha) {
            if ($fecha->isWeekend() || isset($fechasExcluidas[$fecha->toDateString()])) {
                continue;
            }

            $diaSemana = self::DIAS_ORDEN[$fecha->dayOfWeekIso - 1];
            $horario   = $horariosPorDia->get($diaSemana);

            if ($horario === null) {
                continue;
            }

            $horas      = $this->horasDiarias($horario);
            $previstas += $horas;

            $seguimiento = $seguimientosPorFecha->get($fecha->toDateString());

            if ($seguimiento !== null && $seguimiento->confirmado_tutor) {
                $confirmadas += $horas;
            }
        }

        $ajuste = (float) $asignacion->ajustesHoras()
            ->where('semana', $lunesSemana->toDateString())
            ->value('ajuste');

        return [
            'previstas'   => round($previstas, 2),
            'confirmadas' => round($confirmadas, 2),
            'ajuste'      => round($ajuste, 2),
            'realizadas'  => round($confirmadas + $ajuste, 2),
        ];
    }

    public function generarTextoHorario(AsignacionFct $asignacion): string
    {
        $horarios = $asignacion->horarios;

        if ($horarios->isEmpty()) {
            return '';
        }

        $grupos      = [];
        $grupoActual = null;

        foreach ($horarios as $horario) {
            $firma = $this->firmaHorario($horario);

            if ($grupoActual !== null
                && $grupoActual['firma'] === $firma
                && $this->esDiaSiguiente($grupoActual['dias'], $horario->dia)) {
                $grupoActual['dias'][] = $horario->dia;
                continue;
            }

            if ($grupoActual !== null) {
                $grupos[] = $grupoActual;
            }

            $grupoActual = [
                'firma' => $firma,
                'dias'  => [$horario->dia],
                'texto' => $this->textoTramo($horario),
            ];
        }

        if ($grupoActual !== null) {
            $grupos[] = $grupoActual;
        }

        $partes = array_map(function ($grupo) {
            $dias         = $grupo['dias'];
            $etiquetaDias = count($dias) === 1
                ? self::DIAS_LABEL[$dias[0]]
                : self::DIAS_LABEL[$dias[0]] . ' a ' . self::DIAS_LABEL[end($dias)];

            return "{$etiquetaDias} {$grupo['texto']}";
        }, $grupos);

        return implode(', ', $partes);
    }

    public function advertencias(AsignacionFct $asignacion): array
    {
        $advertencias = [];
        $totalSemana  = 0.0;

        foreach ($asignacion->horarios as $horario) {
            $horas        = $this->horasDiarias($horario);
            $totalSemana += $horas;

            if ($horas > self::MAX_HORAS_DIA) {
                $advertencias[] = [
                    'tipo'    => 'exceso_diario',
                    'dia'     => $horario->dia,
                    'mensaje' => sprintf(
                        '%s: %.2fh supera el máximo diario recomendado de %.0fh.',
                        self::DIAS_LABEL[$horario->dia],
                        $horas,
                        self::MAX_HORAS_DIA
                    ),
                ];
            }
        }

        if ($totalSemana > self::MAX_HORAS_SEMANA) {
            $advertencias[] = [
                'tipo'    => 'exceso_semanal',
                'mensaje' => sprintf(
                    'El total semanal de %.2fh supera el máximo recomendado de %.0fh.',
                    $totalSemana,
                    self::MAX_HORAS_SEMANA
                ),
            ];
        }

        return $advertencias;
    }

    public function validarHorarios(array $horarios): array
    {
        $errores    = [];
        $diasVistos = [];

        foreach ($horarios as $i => $fila) {
            $dia = $fila['dia'] ?? null;

            if ($dia === null || !in_array($dia, self::DIAS_ORDEN, true)) {
                $errores[] = 'Fila ' . ($i + 1) . ': día no válido.';
                continue;
            }

            if (isset($diasVistos[$dia])) {
                $errores[] = self::DIAS_LABEL[$dia] . ': día duplicado en el horario.';
                continue;
            }
            $diasVistos[$dia] = true;

            $entradaManana = $fila['entrada_manana'] ?? null;
            $salidaManana  = $fila['salida_manana'] ?? null;
            $entradaTarde  = $fila['entrada_tarde'] ?? null;
            $salidaTarde   = $fila['salida_tarde'] ?? null;

            if ($entradaManana === null || $salidaManana === null) {
                $errores[] = self::DIAS_LABEL[$dia] . ': faltan entrada/salida de mañana.';
                continue;
            }

            if ($this->minutos($entradaManana) >= $this->minutos($salidaManana)) {
                $errores[] = self::DIAS_LABEL[$dia] . ': la entrada de mañana debe ser anterior a la salida.';
                continue;
            }

            $tieneTarde = $entradaTarde !== null || $salidaTarde !== null;

            if ($tieneTarde) {
                if ($entradaTarde === null || $salidaTarde === null) {
                    $errores[] = self::DIAS_LABEL[$dia] . ': si hay turno de tarde, debe indicarse entrada y salida.';
                    continue;
                }

                if ($this->minutos($salidaManana) > $this->minutos($entradaTarde)) {
                    $errores[] = self::DIAS_LABEL[$dia] . ': la mañana y la tarde se solapan.';
                    continue;
                }

                if ($this->minutos($entradaTarde) >= $this->minutos($salidaTarde)) {
                    $errores[] = self::DIAS_LABEL[$dia] . ': la entrada de tarde debe ser anterior a la salida.';
                }
            }
        }

        return $errores;
    }

    public function guardar(AsignacionFct $asignacion, array $horarios): void
    {
        $errores = $this->validarHorarios($horarios);

        if (!empty($errores)) {
            throw ValidationException::withMessages(['horarios' => $errores]);
        }

        $asignacion->horarios()->delete();

        foreach ($horarios as $fila) {
            $asignacion->horarios()->create([
                'dia'            => $fila['dia'],
                'entrada_manana' => $fila['entrada_manana'],
                'salida_manana'  => $fila['salida_manana'],
                'entrada_tarde'  => $fila['entrada_tarde'] ?? null,
                'salida_tarde'   => $fila['salida_tarde'] ?? null,
            ]);
        }

        $asignacion->unsetRelation('horarios');

        $asignacion->update([
            'num_horas' => (int) round($this->horasPrevistas($asignacion)),
            'horario'   => $this->generarTextoHorario($asignacion),
        ]);
    }

    private function diferenciaHoras($inicio, $fin): float
    {
        $inicioCarbon = $inicio instanceof Carbon ? $inicio : Carbon::parse($inicio);
        $finCarbon    = $fin instanceof Carbon ? $fin : Carbon::parse($fin);

        return abs($finCarbon->diffInMinutes($inicioCarbon)) / 60;
    }

    private function minutos($valorHora): int
    {
        $carbon = $valorHora instanceof Carbon ? $valorHora : Carbon::parse($valorHora);

        return ($carbon->hour * 60) + $carbon->minute;
    }

    private function firmaHorario(HorarioAsignacion $horario): string
    {
        return implode('|', [
            $this->formatoHora($horario->entrada_manana),
            $this->formatoHora($horario->salida_manana),
            $this->formatoHora($horario->entrada_tarde),
            $this->formatoHora($horario->salida_tarde),
        ]);
    }

    private function textoTramo(HorarioAsignacion $horario): string
    {
        $texto = $this->formatoHora($horario->entrada_manana) . '-' . $this->formatoHora($horario->salida_manana);

        if ($horario->entrada_tarde !== null && $horario->salida_tarde !== null) {
            $texto .= ' y ' . $this->formatoHora($horario->entrada_tarde) . '-' . $this->formatoHora($horario->salida_tarde);
        }

        return $texto;
    }

    private function formatoHora($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $carbon = $valor instanceof Carbon ? $valor : Carbon::parse($valor);

        return $carbon->format('H:i');
    }

    private function esDiaSiguiente(array $diasGrupo, string $diaNuevo): bool
    {
        $ultimoDia    = end($diasGrupo);
        $indiceUltimo = array_search($ultimoDia, self::DIAS_ORDEN, true);
        $indiceNuevo  = array_search($diaNuevo, self::DIAS_ORDEN, true);

        return $indiceNuevo === $indiceUltimo + 1;
    }
}
