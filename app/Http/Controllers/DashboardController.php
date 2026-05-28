<?php
namespace App\Http\Controllers;

use App\Models\Colocacion;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\CicloFormativo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $cursoActual = Configuracion::cursoActivo();

        $seguimientosPendientes = \App\Models\Contacto::with('empresa')
            ->where('registrado_por_id', $user->id)
            ->whereNotNull('fecha_seguimiento')
            ->where('fecha_seguimiento', '>=', now()->startOfDay())
            ->orderBy('fecha_seguimiento')
            ->take(5)
            ->get();

        if ($user->esAdmin()) {
            $view = $this->dashboardAdmin($request, $cursoActual);
        } elseif ($user->esResponsableFFE()) {
            $view = $this->dashboardResponsableFFE($request, $cursoActual);
        } elseif ($user->esResponsableCiclo()) {
            $view = $this->dashboardResponsableCiclo($request, $user, $cursoActual);
        } else {
            $view = $this->dashboardProfesor($user, $cursoActual);
        }

        return $view->with('seguimientosPendientes', $seguimientosPendientes);
    }
    private function dashboardAdmin(Request $request, string $cursoActual): View
    {
        return $this->dashboardGlobal($request, $cursoActual, 'admin');
    }

    private function dashboardResponsableFFE(Request $request, string $cursoActual): View
    {
        return $this->dashboardGlobal($request, $cursoActual, 'responsable_ffe');
    }

    /**
     * Dashboard global compartido por Admin y Responsable FFE
     */
    private function dashboardGlobal(Request $request, string $cursoActual, string $tipo): View
    {
        $cicloFiltro = $request->get('ciclo');
        $todosLosCiclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();

        if ($cicloFiltro) {
            $ciclosIds = [$cicloFiltro];
            $empresasQuery = Empresa::whereHas('ciclos', fn($q) => $q->whereIn('ciclos_formativos.id', $ciclosIds));
        } else {
            $ciclosIds = null;
            $empresasQuery = Empresa::query();
        }

        $stats = [
            'total_empresas' => (clone $empresasQuery)->count(),
            'convenios_activos' => (clone $empresasQuery)->estadoConvenio('activo')->count(),
            'convenios_por_caducar' => (clone $empresasQuery)->estadoConvenio('por_caducar')->count(),
            'convenios_caducados' => (clone $empresasQuery)->estadoConvenio('caducado')->count(),
        ];

        $empresasUrgentes = (clone $empresasQuery)
            ->estadoConvenio('por_caducar')
            ->with('creador')
            ->orderBy('fecha_firma')
            ->take(5)
            ->get();

        $ultimasEmpresas = (clone $empresasQuery)
            ->with(['creador', 'ciclos'])
            ->latest()
            ->take(5)
            ->get();

        $colocacionesQuery = Colocacion::where('curso_academico', $cursoActual);
        if ($ciclosIds) {
            $colocacionesQuery->whereIn('ciclo_id', $ciclosIds);
        }
        $colocacionesCurso = $colocacionesQuery
            ->selectRaw('SUM(num_alumnos) as total_alumnos, SUM(num_alumnos * num_horas) as total_horas, COUNT(*) as num_envios')
            ->first();

        $ciclos = $todosLosCiclos;

        return view('dashboard', compact(
            'stats', 'empresasUrgentes', 'ultimasEmpresas', 'cursoActual', 'colocacionesCurso', 'ciclos', 'todosLosCiclos', 'cicloFiltro'
        ))->with('dashboardTipo', $tipo);
    }

    /**
     * Dashboard para Responsable de Ciclo - Solo sus ciclos con filtro
     */
    private function dashboardResponsableCiclo(Request $request, $user, string $cursoActual): View
    {
        $ciclosUsuario = $user->ciclos()->orderBy('nivel')->orderBy('nombre')->get();
        $ciclosUsuarioIds = $ciclosUsuario->pluck('id')->toArray();
        
        $cicloFiltro = $request->get('ciclo');
        
        // Si hay filtro, verificar que el ciclo pertenece al usuario
        if ($cicloFiltro && in_array($cicloFiltro, $ciclosUsuarioIds)) {
            $ciclosIds = [$cicloFiltro];
        } else {
            $ciclosIds = $ciclosUsuarioIds;
            $cicloFiltro = null;
        }

        $empresasQuery = Empresa::whereHas('ciclos', fn($q) => $q->whereIn('ciclos_formativos.id', $ciclosIds));

        $stats = [
            'total_empresas' => (clone $empresasQuery)->count(),
            'convenios_activos' => (clone $empresasQuery)->estadoConvenio('activo')->count(),
            'convenios_por_caducar' => (clone $empresasQuery)->estadoConvenio('por_caducar')->count(),
            'convenios_caducados' => (clone $empresasQuery)->estadoConvenio('caducado')->count(),
        ];

        $empresasUrgentes = (clone $empresasQuery)
            ->estadoConvenio('por_caducar')
            ->with('creador')
            ->orderBy('fecha_firma')
            ->take(5)
            ->get();

        $ultimasEmpresas = (clone $empresasQuery)
            ->with(['creador', 'ciclos'])
            ->latest()
            ->take(5)
            ->get();

        $colocacionesCurso = Colocacion::where('curso_academico', $cursoActual)
            ->whereIn('ciclo_id', $ciclosIds)
            ->selectRaw('SUM(num_alumnos) as total_alumnos, SUM(num_alumnos * num_horas) as total_horas, COUNT(*) as num_envios')
            ->first();

        // Mis asignaciones recientes (filtradas por ciclo si aplica)
        $misAsignacionesQuery = Colocacion::where('registrado_por_id', $user->id)
            ->whereIn('ciclo_id', $ciclosIds)
            ->with(['empresa', 'ciclo'])
            ->latest();
        $misAsignaciones = $misAsignacionesQuery->take(5)->get();

        $ciclos = $ciclosUsuario;
        $todosLosCiclos = $ciclosUsuario; // Para el selector, solo sus ciclos

        return view('dashboard', compact(
            'stats', 'empresasUrgentes', 'ultimasEmpresas', 'cursoActual', 'colocacionesCurso', 'ciclos', 'ciclosUsuario', 'misAsignaciones', 'todosLosCiclos', 'cicloFiltro'
        ))->with('dashboardTipo', 'responsable_ciclo');
    }

    /**
     * Dashboard para Profesor - Solo sus empresas
     */
    private function dashboardProfesor($user, string $cursoActual): View
    {
        $misEmpresas = Empresa::where('creador_id', $user->id);

        $stats = [
            'total_empresas' => (clone $misEmpresas)->count(),
            'convenios_activos' => (clone $misEmpresas)->estadoConvenio('activo')->count(),
            'convenios_por_caducar' => (clone $misEmpresas)->estadoConvenio('por_caducar')->count(),
            'convenios_caducados' => (clone $misEmpresas)->estadoConvenio('caducado')->count(),
        ];

        $empresasUrgentes = Empresa::where('creador_id', $user->id)
            ->estadoConvenio('por_caducar')
            ->orderBy('fecha_firma')
            ->take(5)
            ->get();

        $ultimasEmpresas = Empresa::where('creador_id', $user->id)
            ->with(['ciclos'])
            ->latest()
            ->take(5)
            ->get();

        $colocacionesCurso = Colocacion::where('curso_academico', $cursoActual)
            ->where('registrado_por_id', $user->id)
            ->selectRaw('SUM(num_alumnos) as total_alumnos, SUM(num_alumnos * num_horas) as total_horas, COUNT(*) as num_envios')
            ->first();

        $misAsignaciones = Colocacion::where('registrado_por_id', $user->id)
            ->with(['empresa', 'ciclo'])
            ->latest()
            ->take(5)
            ->get();

        $ciclos = collect();
        $todosLosCiclos = collect();
        $cicloFiltro = null;

        return view('dashboard', compact(
            'stats', 'empresasUrgentes', 'ultimasEmpresas', 'cursoActual', 'colocacionesCurso', 'ciclos', 'misAsignaciones', 'todosLosCiclos', 'cicloFiltro'
        ))->with('dashboardTipo', 'profesor');
    }
}
