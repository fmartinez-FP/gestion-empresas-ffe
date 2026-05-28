<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\CicloFormativo;
use App\Models\Colocacion;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportPdfService
{
    public function generarFichaEmpresa(Empresa $empresa): string
    {
        $empresa->load(['creador', 'ciclos', 'colocaciones.ciclo']);
        
        $colocacionesPorCurso = $empresa->colocaciones
            ->sortByDesc('curso_academico')
            ->groupBy('curso_academico');

        $pdf = Pdf::loadView('pdf.ficha-empresa', [
            'empresa' => $empresa,
            'colocacionesPorCurso' => $colocacionesPorCurso,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'ficha_' . preg_replace('/[^a-zA-Z0-9]/', '_', $empresa->nombre) . '_' . date('Ymd') . '.pdf';
        $path = sys_get_temp_dir() . '/' . $filename;
        
        $pdf->save($path);

        return $path;
    }

    public function generarInformeColocaciones(string $cursoAcademico): string
    {
        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        
        $statsAgrupados = Colocacion::where('curso_academico', $cursoAcademico)
            ->selectRaw('ciclo_id, numero_curso, COALESCE(SUM(num_alumnos), 0) as alumnos, COALESCE(SUM(num_horas), 0) as horas')
            ->groupBy('ciclo_id', 'numero_curso')
            ->get()
            ->groupBy('ciclo_id');

        $estadisticas = [];
        $totales = ['alumnos_1' => 0, 'horas_1' => 0, 'alumnos_2' => 0, 'horas_2' => 0];

        foreach ($ciclos as $ciclo) {
            $cicloStats = $statsAgrupados->get($ciclo->id, collect());
            $stats1 = $cicloStats->firstWhere('numero_curso', 1) ?? (object)['alumnos' => 0, 'horas' => 0];
            $stats2 = $cicloStats->firstWhere('numero_curso', 2) ?? (object)['alumnos' => 0, 'horas' => 0];
            $estadisticas[] = [
                'ciclo'   => $ciclo,
                'primero' => $stats1,
                'segundo' => $stats2,
            ];
            $totales['alumnos_1'] += $stats1->alumnos;
            $totales['horas_1']   += $stats1->horas;
            $totales['alumnos_2'] += $stats2->alumnos;
            $totales['horas_2']   += $stats2->horas;
        }

        $pdf = Pdf::loadView('pdf.informe-colocaciones', [
            'cursoAcademico' => $cursoAcademico,
            'estadisticas' => $estadisticas,
            'totales' => $totales,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $filename = 'informe_colocaciones_' . str_replace('-', '_', $cursoAcademico) . '.pdf';
        $path = sys_get_temp_dir() . '/' . $filename;
        
        $pdf->save($path);

        return $path;
    }
}
