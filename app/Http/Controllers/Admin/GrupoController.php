<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\CicloFormativo;
use App\Models\Grupo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GrupoController extends Controller
{
    public function store(Request $request, CicloFormativo $ciclo): RedirectResponse
    {
        $validated = $request->validate([
            'numero_curso' => ['required', 'integer', 'in:1,2'],
            'etiqueta'     => ['nullable', 'string', 'max:10'],
        ]);

        $etiqueta = trim($validated['etiqueta'] ?? '');

        $existe = Grupo::where('ciclo_id', $ciclo->id)
            ->where('numero_curso', $validated['numero_curso'])
            ->where('etiqueta', $etiqueta)
            ->exists();

        if ($existe) {
            $nombre = $validated['numero_curso'] . 'º' . ($etiqueta !== '' ? ' ' . $etiqueta : '');
            return back()
                ->withErrors(['etiqueta' => "Ya existe el grupo {$nombre} en este ciclo."])
                ->withInput();
        }

        $grupo = Grupo::create([
            'ciclo_id'     => $ciclo->id,
            'numero_curso' => $validated['numero_curso'],
            'etiqueta'     => $etiqueta,
            'activo'       => true,
        ]);

        if (Schema::hasTable('auditoria')) {
            Auditoria::registrarCreacion($grupo);
        }

        return redirect()->route('admin.ciclos.edit', $ciclo)
            ->with('success', "Grupo '{$grupo->etiqueta_completa}' creado.");
    }

    public function toggleActivo(CicloFormativo $ciclo, Grupo $grupo): RedirectResponse
    {
        abort_unless($grupo->ciclo_id === $ciclo->id, 404);

        $datosAnt = $grupo->toArray();
        $grupo->update(['activo' => ! $grupo->activo]);

        if (Schema::hasTable('auditoria')) {
            Auditoria::registrarActualizacion($grupo, $datosAnt);
        }

        $estado = $grupo->activo ? 'activado' : 'desactivado';

        return redirect()->route('admin.ciclos.edit', $ciclo)
            ->with('success', "Grupo '{$grupo->etiqueta_completa}' {$estado}.");
    }
}
