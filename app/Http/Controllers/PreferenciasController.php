<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PreferenciasController extends Controller
{
    public function toggleModoOscuro(): JsonResponse
    {
        $user = auth()->user();
        $nuevo = !$user->modoOscuro();
        $user->setPreferencia('modo_oscuro', $nuevo);

        return response()->json(['success' => true, 'modo_oscuro' => $nuevo]);
    }
}
