<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Models\PlanFormativoDatos;
use App\Services\GeneradorPdfFfeService;
use Illuminate\Http\Request;

class PlanFormativoController extends Controller
{
    public function __construct(
        private readonly GeneradorPdfFfeService $generador
    ) {}

    /**
     * Muestra el formulario de datos adicionales para el Plan de Formación.
     * Si ya existen datos vigentes, los prerellena.
     */
    public function form(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('gestionarDocumento', $asignacion), 403);

        $asignacion->loadMissing([
            'alumno', 'empresa', 'ciclo',
            'resultadosAprendizaje.modulo',
            'tutorIes', 'tutorEmpresa',
        ]);

        $datos = $asignacion->planFormativoDatos;

        // Si existen datos pero ya caducaron, ignorarlos
        if ($datos && !$datos->estaVigente()) {
            $datos = null;
        }

        return view('asignaciones.plan-formativo-form', compact('asignacion', 'datos'));
    }

    /**
     * Guarda los datos, genera el PDF y redirige a asignaciones.show.
     */
    public function generar(Request $request, AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('gestionarDocumento', $asignacion), 403);

        $validated = $request->validate([
            'medidas_discapacidad'               => ['nullable', 'boolean'],
            'medidas_discapacidad_detalle'        => ['nullable', 'string', 'max:1000'],
            'autorizacion_extraordinaria'         => ['nullable', 'boolean'],
            'autorizacion_extraordinaria_detalle' => ['nullable', 'string', 'max:1000'],
            'intervalo'                           => ['required', 'in:diario,semanal,mensual,otros,varias_empresas'],
            'periodos'                            => ['nullable', 'string', 'max:2000'],
            'observaciones'                       => ['nullable', 'string', 'max:2000'],
            'formaciones_especificas'             => ['nullable', 'string', 'max:2000'],
            'imparticion_modulos'                 => ['nullable', 'array'],
            'imparticion_modulos.*'               => ['in:integra,compartida'],
        ]);

        // Normalizar booleanos de checkboxes
        $validated['medidas_discapacidad']       = $request->boolean('medidas_discapacidad');
        $validated['autorizacion_extraordinaria'] = $request->boolean('autorizacion_extraordinaria');
        $validated['purgar_after']               = today()->addDays(10);

        // Guardar o actualizar datos
        $asignacion->planFormativoDatos()->updateOrCreate(
            ['asignacion_id' => $asignacion->id],
            $validated
        );

        // Generar PDF pasando datos adicionales
        $asignacion->loadMissing([
            'alumno', 'empresa', 'sede', 'ciclo',
            'tutorIes', 'tutorEmpresa',
            'resultadosAprendizaje.modulo',
            'criteriosEvaluacion.resultadoAprendizaje.modulo',
        ]);

        $documento = $this->generador->generarPlanFormativo(
            $asignacion,
            $validated
        );

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', 'Plan de Formación generado correctamente. Los datos se conservarán 10 días por si necesitas modificarlo.');
    }
}
