<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionFct;
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
            'curso' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', function ($attr, $valor, $fail) {
                // El segundo año debe ser el siguiente al primero (2025-2026, no 2025-2031).
                [$a, $b] = array_map('intval', explode('-', $valor));
                if ($b !== $a + 1) {
                    $fail('El curso debe ser de años consecutivos (por ejemplo 2025-2026).');
                }
            }],
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

        // Al avanzar se borran los no lectivos del centro: con asignaciones activas del curso
        // que termina se perdería el calendario con el que se calculan sus días y horas.
        $activas = AsignacionFct::where('curso_academico', $cursoAnterior)->where('estado', 'activa')->count();
        if ($activas > 0) {
            return redirect()
                ->route('admin.configuracion.curso')
                ->with('error', "No se puede avanzar de curso: quedan {$activas} asignación(es) FFE activas en {$cursoAnterior}. "
                    . 'Finalízalas o cancélalas antes (o ejecuta ffe:reset-curso).');
        }

        Configuracion::setCursoActivo($cursoNuevo);

        // Los no lectivos de centro son propios del curso activo; al avanzar de
        // curso pierden sentido y se eliminan de forma definitiva (decision 2026-08-12).
        \App\Models\NoLectivoIes::withTrashed()->get()->each->forceDelete();

        return redirect()
            ->route('admin.configuracion.curso')
            ->with('success', "Curso avanzado de {$cursoAnterior} a {$cursoNuevo}. Las nuevas colocaciones se registrarán en {$cursoNuevo}.");
    }
}
