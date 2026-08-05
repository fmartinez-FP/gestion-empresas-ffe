<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelarAsignacionRequest;
use App\Http\Requests\StoreAsignacionRequest;
use App\Http\Requests\UpdateAsignacionRequest;
use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\Configuracion;
use App\Models\CriterioEvaluacion;
use App\Models\Empresa;
use App\Models\ResultadoAprendizaje;
use App\Models\User;
use App\Services\OnboardingAlumnoService;
use Illuminate\Http\Request;

class AsignacionFctController extends Controller
{
    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(Alumno $alumno)
    {
        abort_unless(auth()->user()->can('crearAsignacion'), 403);

        $cursoActivo  = Configuracion::cursoActivo();
        $empresas     = Empresa::orderBy('nombre')->get(['id', 'nombre']);
        $tutoresIes   = User::where('activo', true)
                            ->whereIn('rol', ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor'])
                            ->orderBy('nombre')
                            ->get(['id', 'nombre']);

        // RA elegibles del ciclo del alumno para el curso activo, agrupados por módulo
        $modulos = $alumno->ciclo->modulos()
            ->with(['resultadosAprendizaje' => function ($q) use ($cursoActivo) {
                $q->whereHas('elegibles', fn($e) => $e->where('curso_academico', $cursoActivo))
                  ->with(['criterios' => function ($qc) use ($cursoActivo) {
                      $qc->whereHas('elegibles', fn($e) => $e->where('curso_academico', $cursoActivo));
                  }]);
            }])
            ->orderBy('curso')->orderBy('codigo')
            ->get();

        return view('asignaciones.create', compact(
            'alumno', 'cursoActivo', 'empresas', 'tutoresIes', 'modulos'
        ));
    }

    public function store(
        StoreAsignacionRequest $request,
        Alumno $alumno,
        OnboardingAlumnoService $onboarding,
        \App\Services\HorarioAsignacionService $horarioService
    ) {
        $data = $request->validated();

        $asignacion = AsignacionFct::create([
            'alumno_id'        => $alumno->id,
            'empresa_id'       => $data['empresa_id'],
            'sede_id'          => $data['sede_id'] ?? null,
            'tutor_empresa_id' => $data['tutor_empresa_id'] ?? null,
            'tutor_ies_id'     => $data['tutor_ies_id'],
            'ciclo_id'         => $alumno->ciclo_id,
            'curso_academico'  => Configuracion::cursoActivo(),
            'numero_curso'     => $alumno->numero_curso,
            'fecha_inicio'     => $data['fecha_inicio'],
            'fecha_fin'        => $data['fecha_fin'],
            'estado'           => 'activa',
        ]);

        $horarioService->guardar($asignacion, $data['horarios']);

        if (!empty($data['ra_ids'])) {
            $asignacion->resultadosAprendizaje()->sync($data['ra_ids']);
        }
        if (!empty($data['ce_ids'])) {
            $asignacion->criteriosEvaluacion()->sync($data['ce_ids']);
        }

        // Onboarding: crear cuenta alumno si es su primera asignación
        if ($alumno->asignaciones()->count() === 1) {
            $onboarding->crearCuentaAlumno($alumno);
        }

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', 'Asignación FFE creada correctamente.');
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('verAsignacion', $asignacion), 403);

        $asignacion->load([
            'alumno',
            'empresa',
            'sede',
            'tutorEmpresa',
            'tutorIes',
            'ciclo',
            'resultadosAprendizaje.modulo',
            'criteriosEvaluacion.resultadoAprendizaje.modulo',
        ]);

        $puedeEditar   = auth()->user()->can('editarAsignacion', $asignacion);
        $puedeCancelar = auth()->user()->can('cancelarAsignacion', $asignacion);

        return view('asignaciones.show', compact('asignacion', 'puedeEditar', 'puedeCancelar'));
    }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

    public function edit(AsignacionFct $asignacion)
    {
        abort_unless(auth()->user()->can('editarAsignacion', $asignacion), 403);

        $asignacion->load(['alumno.ciclo', 'resultadosAprendizaje', 'criteriosEvaluacion']);

        $cursoActivo = Configuracion::cursoActivo();
        $empresas    = Empresa::orderBy('nombre')->get(['id', 'nombre']);
        $tutoresIes  = User::where('activo', true)
                           ->whereIn('rol', ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor'])
                           ->orderBy('nombre')
                           ->get(['id', 'nombre']);

        // Sede y contactos de la empresa actual para precargar selects
        $sedesEmpresa     = $asignacion->empresa->direcciones()->orderBy('principal', 'desc')->get();
        $contactosEmpresa = $asignacion->empresa->personasContacto()->orderBy('principal', 'desc')->get();

        $modulos = $asignacion->alumno->ciclo->modulos()
            ->with(['resultadosAprendizaje' => function ($q) use ($cursoActivo) {
                $q->whereHas('elegibles', fn($e) => $e->where('curso_academico', $cursoActivo))
                  ->with(['criterios' => function ($qc) use ($cursoActivo) {
                      $qc->whereHas('elegibles', fn($e) => $e->where('curso_academico', $cursoActivo));
                  }]);
            }])
            ->orderBy('curso')->orderBy('codigo')
            ->get();

        $raSeleccionados = $asignacion->resultadosAprendizaje->pluck('id')->toArray();
        $ceSeleccionados = $asignacion->criteriosEvaluacion->pluck('id')->toArray();

        $puedeEditarEstado = in_array(auth()->user()->rol, ['admin', 'responsable_ffe']);

        return view('asignaciones.edit', compact(
            'asignacion', 'cursoActivo', 'empresas', 'tutoresIes',
            'sedesEmpresa', 'contactosEmpresa', 'modulos',
            'raSeleccionados', 'ceSeleccionados', 'puedeEditarEstado'
        ));
    }

    public function update(
        UpdateAsignacionRequest $request,
        AsignacionFct $asignacion,
        \App\Services\HorarioAsignacionService $horarioService
    ) {
        $data = $request->validated();

        $campos = [
            'empresa_id'       => $data['empresa_id'],
            'sede_id'          => $data['sede_id'] ?? null,
            'tutor_empresa_id' => $data['tutor_empresa_id'] ?? null,
            'tutor_ies_id'     => $data['tutor_ies_id'],
            'fecha_inicio'     => $data['fecha_inicio'],
            'fecha_fin'        => $data['fecha_fin'],
        ];

        if (isset($data['estado'])) {
            $campos['estado'] = $data['estado'];
        }

        $asignacion->update($campos);

        $horarioService->guardar($asignacion, $data['horarios']);

        $asignacion->resultadosAprendizaje()->sync($data['ra_ids'] ?? []);
        $asignacion->criteriosEvaluacion()->sync($data['ce_ids'] ?? []);

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', 'Asignación actualizada correctamente.');
    }

    // =========================================================================
    // CANCELAR
    // =========================================================================

    public function cancelar(CancelarAsignacionRequest $request, AsignacionFct $asignacion)
    {
        $asignacion->update([
            'estado'      => 'cancelada',
            'motivo_baja' => $request->validated()['motivo_baja'],
        ]);

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', 'Asignación cancelada.');
    }

    // =========================================================================
    // ENDPOINTS JSON AUXILIARES (para Alpine.js en formularios)
    // =========================================================================

    public function sedes(Empresa $empresa)
    {
        $sedes = $empresa->direcciones()
            ->orderBy('principal', 'desc')
            ->orderBy('municipio')
            ->get(['id', 'tipo_via', 'nombre_via', 'numero', 'municipio', 'principal']);

        return response()->json($sedes->map(fn($d) => [
            'id'    => $d->id,
            'label' => trim(($d->tipo_via ? $d->tipo_via . ' ' : '') . $d->nombre_via . ($d->numero ? ', ' . $d->numero : '') . ($d->municipio ? ' (' . $d->municipio . ')' : '')),
        ]));
    }

    public function contactos(Empresa $empresa)
    {
        $contactos = $empresa->personasContacto()
            ->orderBy('principal', 'desc')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'cargo', 'principal']);

        return response()->json($contactos->map(fn($c) => [
            'id'    => $c->id,
            'label' => $c->nombre . ($c->cargo ? ' — ' . $c->cargo : ''),
        ]));
    }
}
