<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
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

        $alumnos = Alumno::with(['ciclo', 'asignacionActiva.empresa'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%' . $request->string('q') . '%';
                $q->where(function ($sub) use ($termino) {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino)
                        ->orWhere('email', 'like', $termino);
                });
            })
            ->when($request->filled('ciclo_id'), fn($q) => $q->where('ciclo_id', $request->integer('ciclo_id')))
            ->when($request->filled('curso_academico'), fn($q) => $q->where('curso_academico', $request->string('curso_academico')))
            ->when($request->filled('numero_curso'), fn($q) => $q->where('numero_curso', $request->integer('numero_curso')))
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('alumnos.index', compact('alumnos', 'ciclos', 'cursosAcademicos'));
    }

    // =========================================================================
    // CREATE / STORE
    // =========================================================================

    public function create()
    {
        abort_unless(auth()->user()->can('crearAlumno'), 403);

        $ciclos      = CicloFormativo::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $cursoActivo = Configuracion::cursoActivo();

        return view('alumnos.create', compact('ciclos', 'cursoActivo'));
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
        $alumno->load([
            'ciclo',
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

        return view('alumnos.edit', compact('alumno', 'ciclos'));
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
            ->with(['ciclo', 'asignaciones'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%' . $request->string('q') . '%';
                $q->where(function ($sub) use ($termino) {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino);
                });
            })
            ->when($request->filled('ciclo_id'), fn($q) => $q->where('ciclo_id', $request->integer('ciclo_id')))
            ->when($request->filled('curso_academico'), fn($q) => $q->where('curso_academico', $request->string('curso_academico')))
            ->when($request->filled('numero_curso'), fn($q) => $q->where('numero_curso', $request->integer('numero_curso')))
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
        $cursoActivo = Configuracion::cursoActivo();

        return view('alumnos.import', compact('ciclos', 'cursoActivo'));
    }

    /**
     * PENDIENTE — Fase C del Plan de Reconstrucción: ImportAlumnosService no
     * existe. Placeholder no destructivo: no rompe la ruta con un 500, informa
     * con claridad. Reemplazar cuando se resuelva Fase C.
     */
    public function import(Request $request)
    {
        abort_unless(auth()->user()->can('importarAlumnos'), 403);

        return redirect()
            ->route('alumnos.import')
            ->with('import_errores', [
                'La importación todavía no está implementada — pendiente de Fase C '
                . '(ImportAlumnosService, ver Plan de Reconstrucción). No se ha procesado ningún archivo.',
            ]);
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
