<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\CicloFormativo;
use App\Models\Colocacion;
use App\Models\Configuracion;
use App\Services\ExportExcelService;
use App\Services\ExportPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private ExportExcelService $excelService,
        private ExportPdfService $pdfService
    ) {}

    /**
     * Exportar listado de empresas a Excel
     */
    public function empresasExcel(Request $request): BinaryFileResponse
    {
        $filtros = [
            'buscar' => $request->get('buscar'),
            'ciclo' => $request->get('ciclo'),
            'estado' => $request->get('estado'),
        ];

        $path = $this->excelService->exportarEmpresas($filtros);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Exportar ficha de empresa a PDF
     */
    public function empresaPdf(Empresa $empresa): BinaryFileResponse
    {
        $path = $this->pdfService->generarFichaEmpresa($empresa);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Exportar informe de colocaciones a Excel
     */
    public function colocacionesExcel(Request $request): BinaryFileResponse
    {
        $cursoAcademico = $request->get('curso', Configuracion::cursoActivo());

        $path = $this->excelService->exportarInformeColocaciones($cursoAcademico);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Exportar informe de colocaciones a PDF
     */
    public function colocacionesPdf(Request $request): BinaryFileResponse
    {
        $cursoAcademico = $request->get('curso', Configuracion::cursoActivo());

        $path = $this->pdfService->generarInformeColocaciones($cursoAcademico);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Vista de informes estadísticos
     */
    public function informes(Request $request)
    {
        $cursoActual = Configuracion::cursoActivo();
        $cursoSeleccionado = $request->get('curso', $cursoActual);
        $cursosDisponibles = Colocacion::cursosConDatos();
        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();

        // Estadísticas por ciclo — 1 query agrupada (antes: 2 queries x ciclo)
        $statsAgrupados = Colocacion::where('curso_academico', $cursoSeleccionado)
            ->selectRaw('ciclo_id, numero_curso, COALESCE(SUM(num_alumnos), 0) as alumnos, COALESCE(SUM(num_alumnos * num_horas), 0) as horas, COUNT(*) as envios')
            ->groupBy('ciclo_id', 'numero_curso')
            ->get()
            ->groupBy('ciclo_id');

        $estadisticasCiclo = [];
        foreach ($ciclos as $ciclo) {
            $cicloStats = $statsAgrupados->get($ciclo->id, collect());
            $stats1 = $cicloStats->firstWhere('numero_curso', 1) ?? (object)['alumnos' => 0, 'horas' => 0, 'envios' => 0];
            $stats2 = $cicloStats->firstWhere('numero_curso', 2) ?? (object)['alumnos' => 0, 'horas' => 0, 'envios' => 0];
            $estadisticasCiclo[] = [
                'ciclo'         => $ciclo,
                'primero'       => $stats1,
                'segundo'       => $stats2,
                'total_alumnos' => $stats1->alumnos + $stats2->alumnos,
                'total_horas'   => $stats1->horas   + $stats2->horas,
                'total_envios'  => $stats1->envios  + $stats2->envios,
            ];
        }
        

        // Totales generales
        $totales = [
            'alumnos' => array_sum(array_column($estadisticasCiclo, 'total_alumnos')),
            'horas' => array_sum(array_column($estadisticasCiclo, 'total_horas')),
            'envios' => array_sum(array_column($estadisticasCiclo, 'total_envios')),
        ];

        // Empresas más activas
        $empresasActivas = Colocacion::where('curso_academico', $cursoSeleccionado)
            ->selectRaw('empresa_id, SUM(num_alumnos) as total_alumnos')
            ->groupBy('empresa_id')
            ->orderByDesc('total_alumnos')
            ->take(10)
            ->with('empresa')
            ->get();

        // Evolución — 1 query agrupada (antes: 2 queries x curso histórico)
        $evolucionRaw = Colocacion::whereIn('curso_academico', $cursosDisponibles)
            ->selectRaw('curso_academico, numero_curso, COALESCE(SUM(num_alumnos), 0) as alumnos, COALESCE(SUM(num_alumnos * num_horas), 0) as horas')
            ->groupBy('curso_academico', 'numero_curso')
            ->get()
            ->groupBy('curso_academico');

        $evolucion = [];
        foreach ($cursosDisponibles as $curso) {
            $cursoStats = $evolucionRaw->get($curso, collect());
            $stats1 = $cursoStats->firstWhere('numero_curso', 1) ?? (object)['alumnos' => 0, 'horas' => 0];
            $stats2 = $cursoStats->firstWhere('numero_curso', 2) ?? (object)['alumnos' => 0, 'horas' => 0];
            $evolucion[$curso] = [
                'alumnos_1' => (int) $stats1->alumnos,
                'alumnos_2' => (int) $stats2->alumnos,
                'horas_1'   => (int) $stats1->horas,
                'horas_2'   => (int) $stats2->horas,
                'alumnos'   => (int) $stats1->alumnos + (int) $stats2->alumnos,
                'horas'     => (int) $stats1->horas   + (int) $stats2->horas,
            ];
        }
        

        return view('informes.index', compact(
            'cursoActual',
            'cursoSeleccionado',
            'cursosDisponibles',
            'ciclos',
            'estadisticasCiclo',
            'totales',
            'empresasActivas',
            'evolucion'
        ));
    }
}
