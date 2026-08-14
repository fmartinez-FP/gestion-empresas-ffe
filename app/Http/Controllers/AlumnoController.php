<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\Grupo;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    // =========================================================================
    // LISTADO
    // =========================================================================

    public function index(Request $request)
    {
        $ciclos = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);

        $cursosAcademicos = Alumno::query()
            ->distinct()
            ->orderByDesc('curso_academico')
            ->pluck('curso_academico');

        $grupoIdsTutor = collect();
        $sinGruposTutor = false;
        if ($request->user()->esProfesor()) {
            $grupoIdsTutor = $request->user()->gruposTutor()->pluck('grupos.id');
            $sinGruposTutor = $grupoIdsTutor->isEmpty();
        }

        $alumnos = Alumno::with(['grupo.ciclo', 'asignacionActiva.empresa'])
            ->when($request->user()->esProfesor(), function ($q) use ($grupoIdsTutor) {
                $q->whereIn('grupo_id', $grupoIdsTutor)
                  ->where('curso_academico', Configuracion::cursoActivo());
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%' . $request->string('q') . '%';
                $q->where(function ($sub) use ($termino) {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino)
                        ->orWhere('email', 'like', $termino);
                });
            })
            ->when($request->filled('ciclo_id'), fn($q) => $q->whereHas('grupo', fn($sub) => $sub->where('ciclo_id', $request->integer('ciclo_id'))))
            ->when($request->filled('curso_academico'), fn($q) => $q->where('curso_academico', $request->string('curso_academico')))
            ->when($request->filled('numero_curso'), fn($q) => $q->whereHas('grupo', fn($sub) => $sub->where('numero_curso', $request->integer('numero_curso'))))
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('alumnos.index', compact('alumnos', 'ciclos', 'cursosAcademicos', 'sinGruposTutor'));
    }

    // =========================================================================
    // CREATE / STORE
    // =========================================================================

    public function create()
    {
        abort_unless(auth()->user()->can('crearAlumno'), 403);

        $ciclos      = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $grupos      = Grupo::activos()->with('ciclo')->orderBy('numero_curso')->orderBy('etiqueta')->get();
        $cursoActivo = Configuracion::cursoActivo();

        return view('alumnos.create', compact('ciclos', 'grupos', 'cursoActivo'));
    }

    public function store(StoreAlumnoRequest $request)
    {
        $alumno = Alumno::create($request->validated() + ['importado_via' => 'manual']);

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Alumno creado correctamente.');
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(Alumno $alumno)
    {
        abort_unless(auth()->user()->can('verAlumno', $alumno), 403);

        $alumno->load([
            'grupo.ciclo',
            'user',
            'asignaciones.empresa',
            'asignaciones.tutorIes',
        ]);

        return view('alumnos.show', compact('alumno'));
    }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

    public function edit(Alumno $alumno)
    {
        abort_unless(auth()->user()->can('editarAlumno', $alumno), 403);

        $ciclos = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $grupos = Grupo::activos()->with('ciclo')->orderBy('numero_curso')->orderBy('etiqueta')->get();

        return view('alumnos.edit', compact('alumno', 'ciclos', 'grupos'));
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $alumno->update($request->validated());

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Alumno actualizado correctamente.');
    }

    // =========================================================================
    // DESTROY (soft delete → Archivo)
    // =========================================================================

    public function destroy(Alumno $alumno)
    {
        abort_unless(auth()->user()->can('eliminarAlumno'), 403);

        // Desactivar la cuenta de portal si existe, según se promete en la UI
        // ("su cuenta de portal quedará desactivada").
        if ($alumno->user) {
            $alumno->user->update(['activo' => false]);
        }

        $alumno->delete();

        return redirect()
            ->route('alumnos.index')
            ->with('success', 'Alumno dado de baja y trasladado al archivo.');
    }

    // =========================================================================
    // RESETEO DE CONTRASEÑA
    // =========================================================================

    public function resetearPassword(Alumno $alumno)
    {
        abort_unless(auth()->user()->can('resetearPasswordAlumno', $alumno), 403);

        if (! $alumno->user) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'Este alumno no tiene cuenta de portal creada.');
        }

        (new \App\Services\OnboardingAlumnoService())->resetearPassword($alumno);

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Contraseña reseteada. Se ha reenviado el email de bienvenida a ' . $alumno->email . '.');
    }

    // =========================================================================
    // ARCHIVO (alumnos con soft delete)
    // =========================================================================

    public function archivo(Request $request)
    {
        abort_unless(auth()->user()->can('verArchivo'), 403);

        $ciclos = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);

        $cursosAcademicos = Alumno::onlyTrashed()
            ->distinct()
            ->orderByDesc('curso_academico')
            ->pluck('curso_academico');

        $alumnos = Alumno::onlyTrashed()
            ->with(['grupo.ciclo', 'asignaciones'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%' . $request->string('q') . '%';
                $q->where(function ($sub) use ($termino) {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino);
                });
            })
            ->when($request->filled('ciclo_id'), fn($q) => $q->whereHas('grupo', fn($sub) => $sub->where('ciclo_id', $request->integer('ciclo_id'))))
            ->when($request->filled('curso_academico'), fn($q) => $q->where('curso_academico', $request->string('curso_academico')))
            ->when($request->filled('numero_curso'), fn($q) => $q->whereHas('grupo', fn($sub) => $sub->where('numero_curso', $request->integer('numero_curso'))))
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('alumnos.archivo', compact('alumnos', 'ciclos', 'cursosAcademicos'));
    }

    // =========================================================================
    // IMPORTACIÓN
    // =========================================================================

    public function importForm()
    {
        abort_unless(auth()->user()->can('importarAlumnos'), 403);

        $ciclos      = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $grupos      = Grupo::activos()->with('ciclo')->orderBy('numero_curso')->orderBy('etiqueta')->get();
        $cursoActivo = Configuracion::cursoActivo();

        return view('alumnos.import', compact('ciclos', 'grupos', 'cursoActivo'));
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->can('importarAlumnos'), 403);

        $data = $request->validate([
            'archivo'         => ['required', 'file', 'max:5120', 'mimes:csv,txt,xlsx,xls'],
            'grupo_id'        => ['required', 'integer', 'exists:grupos,id'],
            'curso_academico' => ['required', 'regex:/^\\d{4}-\\d{4}$/'],
        ]);

        $resultado = (new \App\Services\ImportAlumnosService())->importar(
            $request->file('archivo')->getRealPath(),
            (int) $data['grupo_id'],
            $data['curso_academico'],
        );

        if (! $resultado['success']) {
            return redirect()
                ->route('alumnos.import')
                ->with('import_errores', [$resultado['mensaje']]);
        }

        return redirect()
            ->route('alumnos.index')
            ->with('status', $resultado['mensaje']);
    }

    public function descargarPlantilla()
    {
        abort_unless(auth()->user()->can('importarAlumnos'), 403);

        $csv = "Nombre,Apellidos,Email,Teléfono\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_alumnos.csv"',
        ]);
    }
}
