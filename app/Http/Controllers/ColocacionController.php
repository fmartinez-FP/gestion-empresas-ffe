<?php

namespace App\Http\Controllers;

use App\Models\Colocacion;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\CicloFormativo;
use App\Models\Auditoria;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreColocacionRequest;
use App\Http\Requests\UpdateColocacionRequest;

class ColocacionController extends Controller
{
    /**
     * Listado de colocaciones (histórico)
     */
    public function index(Request $request): View
    {
        $cursoActivo = Configuracion::cursoActivo();
        
        $query = Colocacion::with(['empresa', 'ciclo', 'registradoPor']);

        // Filtro por curso académico (por defecto el activo)
        $cursoSeleccionado = $request->get('curso', $cursoActivo);
        if ($cursoSeleccionado !== 'todos') {
            $query->cursoAcademico($cursoSeleccionado);
        }

        // Filtro por ciclo
        if ($request->filled('ciclo')) {
            $query->ciclo($request->ciclo);
        }

        // Filtro por empresa
        if ($request->filled('empresa')) {
            $query->where('empresa_id', $request->empresa);
        }

        $colocaciones = $query->orderBy('curso_academico', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        $cursosDisponibles = Colocacion::cursosConDatos();

        // Estadísticas del curso seleccionado
        $estadisticas = $cursoSeleccionado !== 'todos' 
            ? Colocacion::estadisticasPorCiclo($cursoSeleccionado)
            : [];

        return view('colocaciones.index', compact(
            'colocaciones',
            'ciclos',
            'cursosDisponibles',
            'cursoActivo',
            'cursoSeleccionado',
            'estadisticas'
        ));
    }

    /**
     * Formulario de creación (desde empresa)
     */
    public function create(Empresa $empresa): View|RedirectResponse
    {
        // Verificar permisos
        if (!auth()->user()->can('colocar', $empresa)) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para registrar asignaciones en esta empresa.');
        }

        $empresa->load('ciclos');
        $cursoActivo = Configuracion::cursoActivo();

        return view('colocaciones.create', compact('empresa', 'cursoActivo'));
    }

    /**
     * Guardar nueva colocación
     */
    public function store(StoreColocacionRequest $request): RedirectResponse
    {
        $cursoActivo = Configuracion::cursoActivo();
        
        $validated = $request->validated();

        $empresa = Empresa::findOrFail($validated['empresa_id']);

        // Verificar permisos
        if (!auth()->user()->can('colocar', $empresa)) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para registrar asignaciones en esta empresa.');
        }

        // Siempre usar el curso activo
        $colocacion = Colocacion::create([
            ...$validated,
            'curso_academico' => $cursoActivo,
            'registrado_por_id' => auth()->id(),
        ]);

	// Registrar auditoría
        Auditoria::registrarAsignacion($colocacion, "Asignación registrada en {$empresa->nombre}");

        // Notificar al profesor creador si no es el usuario actual
        if ($empresa->creador_id && $empresa->creador_id !== auth()->id()) {
            NotificacionService::crear(
                $empresa->creador_id,
                'colocacion_registrada',
                "Nueva asignación en {$empresa->nombre}",
                route('empresas.show', $empresa),
                'Empresa',
                $empresa->id
            );
        }

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', "Asignación registrada en el curso {$cursoActivo}.");
    }

    /**
     * Formulario de edición
     */
    public function edit(Colocacion $colocacion): View|RedirectResponse
    {
        $empresa = $colocacion->empresa;
        
        // Verificar permisos: admin o quien la registró
        $user = auth()->user();
        if (!$user->esAdmin() && $colocacion->registrado_por_id !== $user->id) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para editar esta colocación.');
        }

        $empresa->load('ciclos');
        $cursoActivo = Configuracion::cursoActivo();

        return view('colocaciones.edit', compact('colocacion', 'empresa', 'cursoActivo'));
    }

    /**
     * Actualizar colocación
     */
    public function update(UpdateColocacionRequest $request, Colocacion $colocacion): RedirectResponse
    {
        $empresa = $colocacion->empresa;
        
        // Verificar permisos: admin o quien la registró
        $user = auth()->user();
        if (!$user->esAdmin() && $colocacion->registrado_por_id !== $user->id) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para editar esta colocación.');
        }

        $validated = $request->validated();

        // Guardar datos anteriores para auditoría
        $datosAnteriores = $colocacion->toArray();

        $colocacion->update($validated);

        // Registrar auditoría
        Auditoria::registrarActualizacion($colocacion, $datosAnteriores, "Asignación actualizada en {$empresa->nombre}");

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', 'Asignación actualizada correctamente.');
    }

    /**
     * Eliminar colocación
     */
    public function destroy(Colocacion $colocacion): RedirectResponse
    {
        $empresa = $colocacion->empresa;

        // Solo admin o el que registró pueden eliminar
        $user = auth()->user();
        $puedeEliminar = $user->esAdmin() || $colocacion->registrado_por_id === $user->id;

        if (!$puedeEliminar) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para eliminar esta asignación.');
        }

        // Registrar auditoría
        Auditoria::registrarAsignacion($colocacion, "Asignación eliminada de {$empresa->nombre}");
	$colocacion->delete();

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', 'Asignación eliminada correctamente.');
    }
}
