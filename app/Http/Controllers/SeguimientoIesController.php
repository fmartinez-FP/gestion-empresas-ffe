<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Services\HorarioAsignacionService;
use Illuminate\Http\Request;

class SeguimientoIesController extends Controller
{
    public function __construct(private HorarioAsignacionService $horarioService) {}

    public function index(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $seguimientos = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->orderByDesc('fecha')
            ->paginate(25);

        $horasRealizadas = $this->horarioService->horasRealizadas($asignacion);
        $horasPrevistas  = $this->horarioService->horasPrevistas($asignacion);
        $horasPendientes = max(0, round($horasPrevistas - $horasRealizadas, 2));

        return view('asignaciones.seguimientos.index', compact(
            'asignacion', 'seguimientos', 'horasRealizadas', 'horasPrevistas', 'horasPendientes'
        ));
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
}
