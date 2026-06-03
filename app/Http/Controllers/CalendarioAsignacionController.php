<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Services\CalendarioAsignacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarioAsignacionController extends Controller
{
    public function __construct(private CalendarioAsignacionService $servicio) {}

    public function index(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $diasEspeciales = CalendarioAsignacion::where('asignacion_id', $asignacion->id)
            ->whereIn('tipo', ['festivo', 'no_lectivo', 'baja'])
            ->orderByDesc('fecha')
            ->get();

        $diasLaborables = $this->servicio->diasLaborables($asignacion);

        return view('asignaciones.calendario.index',
            compact('asignacion', 'diasEspeciales', 'diasLaborables'));
    }

    public function store(Request $request, AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $validated = $request->validate([
            'fecha'  => ['required', 'date'],
            'tipo'   => ['required', 'in:festivo,no_lectivo,baja'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ]);

        $this->servicio->marcarDia(
            $asignacion,
            Carbon::parse($validated['fecha']),
            $validated['tipo'],
            $validated['motivo'] ?? null
        );

        return back()->with('success', 'Día marcado correctamente.');
    }

    public function destroy(AsignacionFct $asignacion, CalendarioAsignacion $calendario)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);
        abort_unless($calendario->asignacion_id === $asignacion->id, 404);

        $this->servicio->eliminarDia($calendario);

        return back()->with('success', 'Día eliminado del calendario.');
    }
}
