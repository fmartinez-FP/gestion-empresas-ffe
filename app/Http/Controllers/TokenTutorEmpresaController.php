<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Services\TokenTutorEmpresaService;

class TokenTutorEmpresaController extends Controller
{
    public function __construct(private TokenTutorEmpresaService $servicio) {}

    public function generar(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('gestionarTokenTutorEmpresa', $asignacion), 403);

        $resultado = $this->servicio->generar($asignacion);

        $url = route('tutor.acceso', ['token' => $resultado['tokenPlano']]);

        return back()->with('token_generado', $url);
    }
}
