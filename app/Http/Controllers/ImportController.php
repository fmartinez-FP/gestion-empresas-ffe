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

        // Se lee directamente del fichero temporal de la subida: no se guarda
        // copia en disco (antes store('temp') escribía en storage/app/private
        // pero se leía de storage/app, y el fichero quedaba huérfano).
        $resultado = $this->importService->importarEmpresas(
            $request->file('archivo')->getRealPath(),
            auth()->id(),
            $request->boolean('actualizar_existentes')
        );

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
