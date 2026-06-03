<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Services\TokenTutorEmpresaService;

class TokenTutorEmpresaController extends Controller
{
    public function __construct(private TokenTutorEmpresaService $servicio) {}

    public function generar(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $token = $this->servicio->generar($asignacion);

        $url = route('tutor.acceso', ['token' => $token->token]);

        return back()->with('token_generado', $url);
    }
}
