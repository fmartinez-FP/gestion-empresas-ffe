<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\CicloFormativo;
use App\Models\Colocacion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportExcelService
{
    public function exportarEmpresas(array $filtros = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Empresas');

        $headers = ['Nombre', 'CIF', 'Teléfono', 'Email', 'Contacto', 'Dirección', 'Nº Convenio', 'Fecha Firma', 'Vencimiento', 'Estado', 'Ciclos', 'Responsable'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $query = Empresa::with(['creador', 'ciclos', 'personasContacto']);
        if (!empty($filtros['buscar'])) $query->buscar($filtros['buscar']);
        if (!empty($filtros['ciclo'])) $query->ciclo($filtros['ciclo']);
        if (!empty($filtros['estado'])) $query->estadoConvenio($filtros['estado']);

        $empresas = $query->orderBy('nombre')->get();

        $row = 2;
        foreach ($empresas as $empresa) {
            $ciclosTexto = $empresa->ciclos->pluck('codigo')->implode(', ');
            $estadoTexto = match($empresa->estado_convenio) {
                'activo' => 'Activo',
                'por_caducar' => 'Por caducar',
                'caducado' => 'Caducado',
                default => 'Sin convenio',
            };

            $sheet->setCellValue('A' . $row, $empresa->nombre);
            $sheet->setCellValue('B' . $row, $empresa->cif);
            $sheet->setCellValue('C' . $row, $empresa->telefono ?? '');
            $sheet->setCellValue('D' . $row, $empresa->email ?? '');
            $sheet->setCellValue('E' . $row, $empresa->persona_contacto ?? '');
            $sheet->setCellValue('F' . $row, $empresa->direccion ?? '');
            $sheet->setCellValue('G' . $row, $empresa->num_convenio ?? '');
            $sheet->setCellValue('H' . $row, $empresa->fecha_firma ? $empresa->fecha_firma->format('d/m/Y') : '');
            $sheet->setCellValue('I' . $row, $empresa->fecha_vencimiento ? $empresa->fecha_vencimiento->format('d/m/Y') : '');
            $sheet->setCellValue('J' . $row, $estadoTexto);
            $sheet->setCellValue('K' . $row, $ciclosTexto);
            $sheet->setCellValue('L' . $row, $empresa->creador->nombre ?? '');

            $estadoColor = match($empresa->estado_convenio) {
                'activo' => 'D1FAE5',
                'por_caducar' => 'FEF3C7',
                'caducado' => 'FEE2E2',
                default => 'F3F4F6',
            };
            $sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($estadoColor);
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A2:L' . $lastRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'empresas_' . date('Y-m-d_His') . '.xlsx';
        $path = sys_get_temp_dir() . '/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    public function exportarInformeColocaciones(string $cursoAcademico): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');

        $sheet->setCellValue('A1', 'Informe de Colocaciones - Curso ' . $cursoAcademico);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->setCellValue('A2', 'Generado: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:H2');

        $headers = ['Ciclo', 'Nivel', '1º Alumnos', '1º Horas', '2º Alumnos', '2º Horas', 'Total Alumnos', 'Total Horas'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);

        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        $statsAgrupados = Colocacion::where('curso_academico', $cursoAcademico)
            ->selectRaw('ciclo_id, numero_curso, COALESCE(SUM(num_alumnos), 0) as alumnos, COALESCE(SUM(num_horas), 0) as horas')
            ->groupBy('ciclo_id', 'numero_curso')
            ->get()
            ->groupBy('ciclo_id');

        $row = 5;
        $totales = ['alumnos_1' => 0, 'horas_1' => 0, 'alumnos_2' => 0, 'horas_2' => 0];

        foreach ($ciclos as $ciclo) {
            $cicloStats = $statsAgrupados->get($ciclo->id, collect());
            $stats1 = $cicloStats->firstWhere('numero_curso', 1) ?? (object)['alumnos' => 0, 'horas' => 0];
            $stats2 = $cicloStats->firstWhere('numero_curso', 2) ?? (object)['alumnos' => 0, 'horas' => 0];

            $nivelTexto = match($ciclo->nivel) {
                'basica'   => 'FP Básica',
                'media'    => 'Grado Medio',
                'superior' => 'Grado Superior',
                default    => $ciclo->nivel,
            };

            $sheet->setCellValue('A' . $row, $ciclo->codigo . ' - ' . $ciclo->nombre);
            $sheet->setCellValue('B' . $row, $nivelTexto);
            $sheet->setCellValue('C' . $row, $stats1->alumnos);
            $sheet->setCellValue('D' . $row, $stats1->horas);
            $sheet->setCellValue('E' . $row, $stats2->alumnos);
            $sheet->setCellValue('F' . $row, $stats2->horas);
            $sheet->setCellValue('G' . $row, '=C' . $row . '+E' . $row);
            $sheet->setCellValue('H' . $row, '=D' . $row . '+F' . $row);

            $totales['alumnos_1'] += $stats1->alumnos;
            $totales['horas_1']   += $stats1->horas;
            $totales['alumnos_2'] += $stats2->alumnos;
            $totales['horas_2']   += $stats2->horas;
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '=SUM(C5:C' . ($row - 1) . ')');
        $sheet->setCellValue('D' . $row, '=SUM(D5:D' . ($row - 1) . ')');
        $sheet->setCellValue('E' . $row, '=SUM(E5:E' . ($row - 1) . ')');
        $sheet->setCellValue('F' . $row, '=SUM(F5:F' . ($row - 1) . ')');
        $sheet->setCellValue('G' . $row, '=SUM(G5:G' . ($row - 1) . ')');
        $sheet->setCellValue('H' . $row, '=SUM(H5:H' . ($row - 1) . ')');

        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sheet->getStyle('A5:H' . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Hoja 2: Detalle
        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Detalle');

        $headers = ['Empresa', 'CIF', 'Ciclo', 'Curso', 'Alumnos', 'Horas', 'Registrado por', 'Fecha'];
        $col = 'A';
        foreach ($headers as $header) {
            $detailSheet->setCellValue($col . '1', $header);
            $col++;
        }
        $detailSheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        $colocaciones = Colocacion::with(['empresa', 'ciclo', 'registradoPor'])
            ->where('curso_academico', $cursoAcademico)
            ->orderBy('created_at', 'desc')
            ->get();

        $row = 2;
        foreach ($colocaciones as $col) {
            $detailSheet->setCellValue('A' . $row, $col->empresa->nombre);
            $detailSheet->setCellValue('B' . $row, $col->empresa->cif);
            $detailSheet->setCellValue('C' . $row, $col->ciclo->codigo);
            $detailSheet->setCellValue('D' . $row, $col->numero_curso . 'º');
            $detailSheet->setCellValue('E' . $row, $col->num_alumnos);
            $detailSheet->setCellValue('F' . $row, $col->num_horas);
            $detailSheet->setCellValue('G' . $row, $col->registradoPor->nombre ?? '');
            $detailSheet->setCellValue('H' . $row, $col->created_at->format('d/m/Y'));
            $row++;
        }

        foreach (range('A', 'H') as $c) {
            $detailSheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'informe_colocaciones_' . str_replace('-', '_', $cursoAcademico) . '.xlsx';
        $path = sys_get_temp_dir() . '/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
