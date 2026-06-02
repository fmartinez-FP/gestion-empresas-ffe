<?php

namespace App\Http\Controllers;

use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\ElegibleFfe;
use App\Models\ElegibleFfeCe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElegibleFfeController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('gestionarElegibles'), 403);

        $cursoActivo = Configuracion::cursoActivo();
        $cursoSeleccionado = $request->get('curso', $cursoActivo);

        $ciclos = CicloFormativo::with(['modulos.resultadosAprendizaje' => function ($query) use ($cursoSeleccionado) {
            $query->with([
                'elegibles' => function ($q) use ($cursoSeleccionado) {
                    $q->where('curso_academico', $cursoSeleccionado);
                },
                'criterios.elegibles' => function ($q) use ($cursoSeleccionado) {
                    $q->where('curso_academico', $cursoSeleccionado);
                },
            ]);
        }])->orderBy('nombre')->get();

        $cursosDisponibles = $this->cursosDisponibles($cursoActivo);

        return view('curriculum.elegibles.index', compact(
            'ciclos',
            'cursoSeleccionado',
            'cursoActivo',
            'cursosDisponibles'
        ));
    }

    public function toggle(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('gestionarElegibles'), 403);

        $validated = $request->validate([
            'resultado_aprendizaje_id' => ['required', 'integer', 'exists:resultados_aprendizaje,id'],
            'curso_academico'          => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ]);

        $existing = ElegibleFfe::where('resultado_aprendizaje_id', $validated['resultado_aprendizaje_id'])
            ->where('curso_academico', $validated['curso_academico'])
            ->first();

        if ($existing) {
            // Al desmarcar RA, desmarcar también todos sus CE elegibles
            ElegibleFfeCe::whereHas('criterioEvaluacion', function ($q) use ($validated) {
                $q->where('resultado_aprendizaje_id', $validated['resultado_aprendizaje_id']);
            })->where('curso_academico', $validated['curso_academico'])->delete();

            $existing->delete();
            $esElegible = false;
        } else {
            ElegibleFfe::create([
                'resultado_aprendizaje_id' => $validated['resultado_aprendizaje_id'],
                'curso_academico'          => $validated['curso_academico'],
                'created_by_id'            => auth()->id(),
            ]);
            $esElegible = true;
        }

        return response()->json(['elegible' => $esElegible]);
    }

    public function toggleCe(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('gestionarElegibles'), 403);

        $validated = $request->validate([
            'criterio_evaluacion_id' => ['required', 'integer', 'exists:criterios_evaluacion,id'],
            'curso_academico'        => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ]);

        $existing = ElegibleFfeCe::where('criterio_evaluacion_id', $validated['criterio_evaluacion_id'])
            ->where('curso_academico', $validated['curso_academico'])
            ->first();

        if ($existing) {
            $existing->delete();
            $esElegible = false;
        } else {
            ElegibleFfeCe::create([
                'criterio_evaluacion_id' => $validated['criterio_evaluacion_id'],
                'curso_academico'        => $validated['curso_academico'],
                'created_by_id'          => auth()->id(),
            ]);
            $esElegible = true;
        }

        return response()->json(['elegible' => $esElegible]);
    }

    private function cursosDisponibles(string $cursoActivo): array
    {
        [$inicio] = explode('-', $cursoActivo);
        $inicio = (int) $inicio;
        $cursos = [];
        for ($i = 0; $i <= 2; $i++) {
            $cursos[] = ($inicio - $i) . '-' . ($inicio - $i + 1);
        }
        return $cursos;
    }
}
