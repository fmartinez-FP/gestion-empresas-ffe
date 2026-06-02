<?php

namespace App\Http\Controllers;

use App\Models\ModuloProfesional;
use App\Models\ResultadoAprendizaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultadoAprendizajeController extends Controller
{
    public function index(ModuloProfesional $modulo): View
    {
        abort_unless(auth()->user()->can('verCurriculum'), 403);

        $modulo->load(['ciclo', 'resultadosAprendizaje.criterios']);

        return view('curriculum.ra.index', compact('modulo'));
    }

    public function store(Request $request, ModuloProfesional $modulo): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $validated = $request->validate([
            'codigo'      => ['required', 'string', 'max:10'],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $modulo->resultadosAprendizaje()->create($validated);

        return redirect()
            ->route('admin.curriculum.ra.index', $modulo)
            ->with('success', 'Resultado de aprendizaje creado correctamente.');
    }

    public function update(Request $request, ResultadoAprendizaje $ra): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $validated = $request->validate([
            'codigo'      => ['required', 'string', 'max:10'],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $ra->update($validated);

        return redirect()
            ->route('admin.curriculum.ra.index', $ra->modulo_id)
            ->with('success', 'RA actualizado correctamente.');
    }

    public function destroy(ResultadoAprendizaje $ra): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $moduloId = $ra->modulo_id;

        if ($ra->asignaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar un RA que está asignado a prácticas.');
        }

        if ($ra->elegibles()->exists()) {
            $ra->elegibles()->delete();
        }

        $ra->criterios()->delete();
        $ra->delete();

        return redirect()
            ->route('admin.curriculum.ra.index', $moduloId)
            ->with('success', 'RA eliminado.');
    }
}
