<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoDiario;
use App\Models\TokenTutorEmpresa;
use App\Services\TokenTutorEmpresaService;
use Illuminate\Http\Request;

class TutorEmpresaController extends Controller
{
    public function __construct(private TokenTutorEmpresaService $servicio) {}

    public function acceso(string $token)
    {
        $registro = $this->servicio->validar($token);

        if ($registro === null) {
            abort(404, 'El enlace no es válido o ha expirado.');
        }

        // Registrar primer uso
        if (! $registro->fueUsado()) {
            $this->servicio->marcarUsado($registro, request()->ip());
        }

        $asignacion  = $registro->asignacion->load(['alumno', 'empresa']);
        $seguimientos = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->orderByDesc('fecha')
            ->get();

        return view('tutor.acceso', compact('registro', 'asignacion', 'seguimientos'));
    }

    public function comentar(Request $request, string $token, SeguimientoDiario $seguimiento)
    {
        $registro = $this->servicio->validar($token);

        if ($registro === null) {
            abort(404, 'El enlace no es válido o ha expirado.');
        }

        abort_unless($seguimiento->asignacion_id === $registro->asignacion_id, 403);

        $validated = $request->validate([
            'comentario_tutor' => ['required', 'string', 'max:500'],
        ]);

        // El tutor empresa puede añadir comentario pero no confirma (eso es del tutor IES)
        $seguimiento->update([
            'comentario_tutor' => $validated['comentario_tutor'],
        ]);

        return back()->with('success', 'Comentario guardado.');
    }
}
