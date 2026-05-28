<?php

namespace App\Http\Controllers;

use App\Models\Valoracion;
use App\Models\Empresa;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreValoracionRequest;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ValoracionController extends Controller
{
    use AuthorizesRequests;
    public function createOrEdit(Empresa $empresa)
    {
        abort_unless(auth()->user()->can('update', $empresa), 403);

        $cursoActivo = Configuracion::cursoActivo();
        $valoracion  = $empresa->valoraciones()->where('curso_academico', $cursoActivo)->first();
        $criterios   = Valoracion::getCriterios();

        return view('valoraciones.form', compact('empresa', 'valoracion', 'criterios', 'cursoActivo'));
    }

    public function store(StoreValoracionRequest $request, Empresa $empresa)
    {
        abort_unless(auth()->user()->can('update', $empresa), 403);

        $validated   = $request->validated();
        $cursoActivo = Configuracion::cursoActivo();

        Valoracion::updateOrCreate(
            ['empresa_id' => $empresa->id, 'curso_academico' => $cursoActivo],
            array_merge($validated, ['valorado_por_id' => auth()->id()])
        );

        return redirect()->route('empresas.show', $empresa)
            ->with('success', "Valoración {$cursoActivo} guardada correctamente.");
    }

    public function destroy(Empresa $empresa)
    {
        abort_unless(auth()->user()->can('update', $empresa), 403);

        $cursoActivo = Configuracion::cursoActivo();
        $empresa->valoraciones()->where('curso_academico', $cursoActivo)->delete();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Valoración del curso actual eliminada correctamente.');
    }
}
