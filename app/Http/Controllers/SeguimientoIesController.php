<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use Illuminate\Http\Request;

class SeguimientoIesController extends Controller
{
    public function index(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $seguimientos = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->orderByDesc('fecha')
            ->paginate(25);

        return view('asignaciones.seguimientos.index', compact('asignacion', 'seguimientos'));
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
