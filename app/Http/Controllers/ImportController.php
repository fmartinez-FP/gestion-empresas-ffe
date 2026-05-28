<?php

namespace App\Http\Controllers;

use App\Services\ImportExcelService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ImportController extends Controller
{
    public function __construct(protected ImportExcelService $importService) {}

    public function index(): View
    {
        return view('empresas.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $ruta = $request->file('archivo')->store('temp');
        $resultado = $this->importService->importarEmpresas(
            storage_path('app/' . $ruta),
            auth()->id(),
            $request->boolean('actualizar_existentes')
        );
        @unlink(storage_path('app/' . $ruta));

        if (!$resultado['success']) {
            return back()->with('error', $resultado['mensaje']);
        }

        $msg = "Importación: {$resultado['importadas']} nuevas, {$resultado['actualizadas']} actualizadas, {$resultado['omitidas']} omitidas.";
        if (!empty($resultado['errores'])) {
            session()->flash('errores_importacion', $resultado['errores']);
        }

        return redirect()->route('empresas.index')->with('success', $msg);
    }

    public function plantilla()
    {
        return response()->download($this->importService->generarPlantilla(), 'plantilla_importacion.xlsx')
            ->deleteFileAfterSend(true);
    }
}
