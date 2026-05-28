<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CicloFormativo;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCicloFormativoRequest;
use App\Http\Requests\UpdateCicloFormativoRequest;
use Illuminate\Support\Facades\Schema;

class CicloFormativoController extends Controller
{
    public function index(): View
    {
        $ciclos = CicloFormativo::withCount(['empresas', 'colocaciones'])
            ->orderBy('nivel')->orderBy('nombre')->get();
        return view('admin.ciclos.index', compact('ciclos'));
    }

    public function create(): View
    {
        return view('admin.ciclos.create');
    }

    public function store(StoreCicloFormativoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo');

        $ciclo = CicloFormativo::create($validated);
        
        if (Schema::hasTable('auditoria')) {
            Auditoria::registrarCreacion($ciclo);
        }

        return redirect()->route('admin.ciclos.index')->with('success', "Ciclo '{$ciclo->nombre}' creado.");
    }

    public function edit(CicloFormativo $ciclo): View
    {
        $ciclo->loadCount(['empresas', 'colocaciones']);
        return view('admin.ciclos.edit', compact('ciclo'));
    }

    public function update(UpdateCicloFormativoRequest $request, CicloFormativo $ciclo): RedirectResponse
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo');

        $datosAnt = $ciclo->toArray();
        $ciclo->update($validated);
        
        if (Schema::hasTable('auditoria')) {
            Auditoria::registrarActualizacion($ciclo, $datosAnt);
        }

        return redirect()->route('admin.ciclos.index')->with('success', "Ciclo '{$ciclo->nombre}' actualizado.");
    }

    public function destroy(CicloFormativo $ciclo): RedirectResponse
    {
        if ($ciclo->empresas()->exists() || $ciclo->colocaciones()->exists()) {
            return redirect()->route('admin.ciclos.index')
                ->with('error', "No se puede eliminar '{$ciclo->nombre}': tiene datos asociados.");
        }

        $nombre = $ciclo->nombre;
        
        if (Schema::hasTable('auditoria')) {
            Auditoria::registrarEliminacion($ciclo);
        }
        
        $ciclo->delete();

        return redirect()->route('admin.ciclos.index')->with('success', "Ciclo '{$nombre}' eliminado.");
    }
}
