<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Colocacion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function cursoActivo(): View
    {
        $cursoActivo = Configuracion::cursoActivo();
        $cursoSiguiente = Configuracion::cursoSiguiente();
        $cursosDisponibles = Configuracion::cursosDisponibles();
        
        // Estadísticas del curso activo
        $estadisticas = [
            'colocaciones' => Colocacion::where('curso_academico', $cursoActivo)->count(),
	    'alumnos' => Colocacion::where('curso_academico', $cursoActivo)->sum('num_alumnos'),
	    'horas' => Colocacion::where('curso_academico', $cursoActivo)->selectRaw('SUM(num_alumnos * num_horas) as total')->value('total') ?? 0,
        ];
        
        return view('admin.configuracion.curso', compact(
            'cursoActivo', 
            'cursoSiguiente', 
            'cursosDisponibles',
            'estadisticas'
        ));
    }

    public function cambiarCurso(Request $request): RedirectResponse
    {
        $request->validate([
            'curso' => 'required|string|regex:/^\d{4}-\d{4}$/',
        ]);

        $cursoAnterior = Configuracion::cursoActivo();
        Configuracion::setCursoActivo($request->curso);

        return redirect()
            ->route('admin.configuracion.curso')
            ->with('success', "Curso activo cambiado de {$cursoAnterior} a {$request->curso}.");
    }

    public function avanzarCurso(): RedirectResponse
    {
        $cursoAnterior = Configuracion::cursoActivo();
        $cursoNuevo = Configuracion::cursoSiguiente();
        
        Configuracion::setCursoActivo($cursoNuevo);

        return redirect()
            ->route('admin.configuracion.curso')
            ->with('success', "Curso avanzado de {$cursoAnterior} a {$cursoNuevo}. Las nuevas colocaciones se registrarán en {$cursoNuevo}.");
    }
}
