<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auditoria::with('user')->orderByDesc('created_at');

        $this->aplicarFiltros($query, $request);

        $registros = $query->paginate(50)->withQueryString();
        $modelos = Auditoria::distinct()->pluck('modelo');
        $usuarios = User::whereIn('id', Auditoria::distinct()->pluck('user_id'))->pluck('nombre', 'id');

        return view('admin.auditoria.index', compact('registros', 'modelos', 'usuarios'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $query = Auditoria::with('user')->orderByDesc('created_at');
        $this->aplicarFiltros($query, $request);
        $registros = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeceras
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Usuario');
        $sheet->setCellValue('C1', 'Acción');
        $sheet->setCellValue('D1', 'Modelo');
        $sheet->setCellValue('E1', 'Descripción');
        $sheet->setCellValue('F1', 'IP');

        // Estilo cabeceras
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');

        // Datos
        $row = 2;
        foreach ($registros as $r) {
            $sheet->setCellValue('A' . $row, $r->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('B' . $row, $r->user_nombre);
            $sheet->setCellValue('C' . $row, $r->accion_etiqueta);
            $sheet->setCellValue('D' . $row, $r->modelo);
            $sheet->setCellValue('E' . $row, $r->descripcion);
            $sheet->setCellValue('F' . $row, $r->ip);
            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'auditoria_' . date('Y-m-d_His') . '.xlsx';
        $path = storage_path('app/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request): BinaryFileResponse
    {
        $query = Auditoria::with('user')->orderByDesc('created_at');
        $this->aplicarFiltros($query, $request);
        $registros = $query->get();

        $pdf = Pdf::loadView('admin.auditoria.pdf', compact('registros'));
        $pdf->setPaper('A4', 'landscape');

        $filename = 'auditoria_' . date('Y-m-d_His') . '.pdf';
        $path = storage_path('app/' . $filename);
        $pdf->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function aplicarFiltros($query, Request $request): void
    {
        if ($request->filled('modelo')) {
            $query->where('modelo', $request->modelo);
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
    }
}
