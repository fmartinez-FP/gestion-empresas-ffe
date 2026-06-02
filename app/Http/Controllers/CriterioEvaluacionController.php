<?php

namespace App\Http\Controllers;

use App\Models\CriterioEvaluacion;
use App\Models\ResultadoAprendizaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CriterioEvaluacionController extends Controller
{
    public function store(Request $request, ResultadoAprendizaje $ra): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $validated = $request->validate([
            'codigo'      => ['required', 'string', 'max:10'],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $ra->criterios()->create($validated);

        return redirect()
            ->route('admin.curriculum.ra.index', $ra->modulo_id)
            ->with('success', 'Criterio de evaluación añadido.');
    }

    public function update(Request $request, CriterioEvaluacion $ce): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $validated = $request->validate([
            'codigo'      => ['required', 'string', 'max:10'],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $ce->update($validated);

        $moduloId = $ce->resultadoAprendizaje->modulo_id;

        return redirect()
            ->route('admin.curriculum.ra.index', $moduloId)
            ->with('success', 'Criterio de evaluación actualizado.');
    }

    public function destroy(CriterioEvaluacion $ce): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $moduloId = $ce->resultadoAprendizaje->modulo_id;

        if ($ce->asignaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar un CE que está asignado a prácticas.');
        }

        $ce->delete();

        return redirect()
            ->route('admin.curriculum.ra.index', $moduloId)
            ->with('success', 'Criterio de evaluación eliminado.');
    }
}
