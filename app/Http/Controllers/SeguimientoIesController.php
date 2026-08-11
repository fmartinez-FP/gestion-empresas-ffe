<?php

namespace App\Http\Controllers;

use App\Models\AjusteHorasSemana;
use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Services\CalendarioAsignacionService;
use App\Services\CuadernoCalendarioService;
use App\Services\HorarioAsignacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SeguimientoIesController extends Controller
{
    public function __construct(
        private HorarioAsignacionService $horarioService,
        private CuadernoCalendarioService $calendarioService,
        private CalendarioAsignacionService $calendarioAsignacionService,
    ) {}

    public function index(AsignacionFct $asignacion, Request $request)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $mesActual = $this->calendarioService->resolverMesOverview($asignacion, $request->query('mes'));

        $diasMes    = $this->calendarioService->diasDelMes($asignacion, $mesActual);
        $estadosMes = $this->calendarioService->resolverEstados($asignacion, $diasMes);
        $semanasMes = $this->calendarioService->agruparPorSemana($diasMes);

        $semanas = $semanasMes->map(function ($diasSemana, $lunes) use ($asignacion, $estadosMes) {
            $lunesCarbon = Carbon::parse($lunes);

            $diasData = $diasSemana->map(fn (Carbon $fecha) => array_merge(
                ['fecha' => $fecha],
                $estadosMes->get($fecha->toDateString())
            ));

            return [
                'lunes' => $lunesCarbon,
                'dias'  => $diasData,
                'horas' => $this->horarioService->horasSemana($asignacion, $lunesCarbon),
            ];
        })->values();

        $horasRealizadas = $this->horarioService->horasRealizadas($asignacion);
        $horasPrevistas  = $this->horarioService->horasPrevistas($asignacion);
        $horasPendientes = max(0, round($horasPrevistas - $horasRealizadas, 2));

        $puedeAjustar          = auth()->user()->can('ajustarHorasSemana', $asignacion);
        $puedeMarcarNoTrabajado = auth()->user()->can('marcarDiaNoTrabajado', $asignacion);

        return view('asignaciones.seguimientos.index', [
            'asignacion'             => $asignacion,
            'semanas'                => $semanas,
            'puedeAjustar'           => $puedeAjustar,
            'puedeMarcarNoTrabajado' => $puedeMarcarNoTrabajado,
            'mesActual'              => $mesActual,
            'estadosMes'             => $estadosMes,
            'horasRealizadas'        => $horasRealizadas,
            'horasPrevistas'         => $horasPrevistas,
            'horasPendientes'        => $horasPendientes,
        ]);
    }

    public function confirmar(Request $request, AsignacionFct $asignacion, SeguimientoDiario $seguimiento)
    {
        abort_unless($seguimiento->asignacion_id === $asignacion->id, 404);
        abort_unless(auth()->user()->can('confirmarSeguimiento', $seguimiento), 403);

        $validated = $request->validate([
            'comentario_tutor' => ['nullable', 'string', 'max:500'],
        ]);

        $seguimiento->update([
            'confirmado_tutor' => true,
            'confirmado_at'    => now(),
            'comentario_tutor' => $validated['comentario_tutor'] ?? null,
        ]);

        return back()->with('success', 'Entrada confirmada.');
    }

    public function ajustarHorasSemana(Request $request, AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('ajustarHorasSemana', $asignacion), 403);

        $validated = $request->validate([
            'semana' => ['required', 'date'],
            'ajuste' => ['required', 'numeric', 'between:-99.99,99.99'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $lunes = Carbon::parse($validated['semana'])->startOfWeek(Carbon::MONDAY);

        AjusteHorasSemana::updateOrCreate(
            ['asignacion_id' => $asignacion->id, 'semana' => $lunes->toDateString()],
            [
                'ajuste'        => $validated['ajuste'],
                'motivo'        => $validated['motivo'] ?? null,
                'created_by_id' => auth()->id(),
            ]
        );

        return back()->with('success', 'Ajuste de horas guardado.');
    }

    public function marcarNoTrabajado(Request $request, AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('marcarDiaNoTrabajado', $asignacion), 403);

        $validated = $request->validate([
            'fecha'  => ['required', 'date'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ]);

        $fecha = Carbon::parse($validated['fecha']);

        abort_if(
            SeguimientoDiario::where('asignacion_id', $asignacion->id)
                ->where('fecha', $fecha->toDateString())
                ->exists(),
            422,
            'El alumno ya tiene una entrada registrada ese dia.'
        );

        $this->calendarioAsignacionService->marcarDia(
            $asignacion,
            $fecha,
            'ausencia_no_justificada',
            $validated['motivo'] ?? null
        );

        return back()->with('success', 'Dia marcado como no trabajado.');
    }

}
