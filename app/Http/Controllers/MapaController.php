<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MapaController extends Controller
{
    public function datos(Request $request): JsonResponse
    {
        $col = 'ciclos_formativos' . '.id';

        $empresas = Empresa::with([
                'direcciones' => fn($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'),
                'ciclos',
            ])
            ->whereHas('direcciones', fn($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'))
            ->when($request->estado, fn($q) => $q->estadoConvenio($request->estado))
            ->when($request->ciclo, fn($q) => $q->whereHas('ciclos',
                fn($q2) => $q2->where($col, $request->ciclo)
            ))
            ->when($request->curso === 'primero', fn($q) => $q->whereHas('ciclos',
                fn($q2) => $q2->wherePivot('acepta_primero', true)
            ))
            ->when($request->curso === 'segundo', fn($q) => $q->whereHas('ciclos',
                fn($q2) => $q2->wherePivot('acepta_segundo', true)
            ))
            ->get();

        $puntos = $empresas->flatMap(function (Empresa $empresa) {
            $niveles = $empresa->ciclos
                ->pluck('nivel')
                ->unique()
                ->sort()
                ->values()
                ->implode('+');

            return $empresa->direcciones->map(fn($d) => [
                'id'        => $empresa->id,
                'nombre'    => $empresa->nombre,
                'url'       => route('empresas.show', $empresa->id),
                'lat'       => (float) $d->latitud,
                'lng'       => (float) $d->longitud,
                'direccion' => $d->formato_completo,
                'estado'    => $empresa->estado_convenio,
                'principal' => $d->principal,
                'niveles'   => $niveles,
            ]);
        })->values();

        return response()->json($puntos);
    }
}
