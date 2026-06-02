<?php

namespace App\Http\Controllers;

use App\Models\CicloFormativo;
use App\Models\ModuloProfesional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuloProfesionalController extends Controller
{
    public function index(CicloFormativo $ciclo): View
    {
        abort_unless(auth()->user()->can('verCurriculum'), 403);

        $modulos = $ciclo->modulos()
            ->withCount('resultadosAprendizaje')
            ->orderBy('curso')
            ->orderBy('codigo')
            ->get();

        return view('curriculum.modulos.index', compact('ciclo', 'modulos'));
    }

    public function store(Request $request, CicloFormativo $ciclo): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);

        $validated = $request->validate([
            'codigo'        => ['required', 'string', 'max:20'],
            'nombre'        => ['required', 'string', 'max:200'],
            'horas_totales' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'curso'         => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $ciclo->modulos()->create($validated);

        return redirect()
            ->route('admin.curriculum.modulos.index', $ciclo)
            ->with('success', 'Módulo profesional creado correctamente.');
    }

    public function edit(CicloFormativo $ciclo, ModuloProfesional $modulo): View
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);
        abort_unless($modulo->ciclo_id === $ciclo->id, 404);

        return view('curriculum.modulos.edit', compact('ciclo', 'modulo'));
    }

    public function update(Request $request, CicloFormativo $ciclo, ModuloProfesional $modulo): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);
        abort_unless($modulo->ciclo_id === $ciclo->id, 404);

        $validated = $request->validate([
            'codigo'        => ['required', 'string', 'max:20'],
            'nombre'        => ['required', 'string', 'max:200'],
            'horas_totales' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'curso'         => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $modulo->update($validated);

        return redirect()
            ->route('admin.curriculum.modulos.index', $ciclo)
            ->with('success', 'Módulo actualizado correctamente.');
    }

    public function destroy(CicloFormativo $ciclo, ModuloProfesional $modulo): RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarCurriculum'), 403);
        abort_unless($modulo->ciclo_id === $ciclo->id, 404);

        if ($modulo->resultadosAprendizaje()->exists()) {
            return back()->with('error', 'No se puede eliminar un módulo que tiene resultados de aprendizaje asociados.');
        }

        $modulo->delete();

        return redirect()
            ->route('admin.curriculum.modulos.index', $ciclo)
            ->with('success', 'Módulo eliminado.');
    }
}
