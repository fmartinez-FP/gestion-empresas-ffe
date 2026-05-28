<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusquedaController extends Controller
{
    public function buscar(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        return response()->json(
            Empresa::buscar($q)
                ->with('ciclos:id,codigo')
                ->select('id', 'nombre', 'cif')
                ->take(10)
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'cif' => $e->cif,
                    'ciclos' => $e->ciclos->pluck('codigo')->implode(', '),
                    'url' => route('empresas.show', $e),
                ])
        );
    }
}
