<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PersonaContacto;
use App\Http\Requests\StorePersonaContactoRequest;
use App\Http\Requests\UpdatePersonaContactoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonaContactoController extends Controller
{
    private function verificarPermiso(Empresa $empresa): ?RedirectResponse
    {
        if (!auth()->user()->can('verPersonasContacto', $empresa)) {
            return redirect()->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para gestionar las personas de contacto de esta empresa.');
        }
        return null;
    }

    public function create(Empresa $empresa): View|RedirectResponse
    {
        if ($redir = $this->verificarPermiso($empresa)) return $redir;
        return view('personas-contacto.create', compact('empresa'));
    }

    public function store(StorePersonaContactoRequest $request, Empresa $empresa): RedirectResponse
    {
        if ($redir = $this->verificarPermiso($empresa)) return $redir;

        $validated = $request->validated();

        // Primera persona → principal automático
        if ($empresa->personasContacto()->count() === 0) {
            $validated['principal'] = true;
        } elseif (!empty($validated['principal'])) {
            // Desmarcar la anterior principal
            $empresa->personasContacto()->update(['principal' => false]);
        }

        $empresa->personasContacto()->create($validated);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Persona de contacto añadida correctamente.');
    }

    public function edit(Empresa $empresa, PersonaContacto $persona): View|RedirectResponse
    {
        if ($redir = $this->verificarPermiso($empresa)) return $redir;
        if ($persona->empresa_id !== $empresa->id) abort(404);

        return view('personas-contacto.edit', compact('empresa', 'persona'));
    }

    public function update(UpdatePersonaContactoRequest $request, Empresa $empresa, PersonaContacto $persona): RedirectResponse
    {
        if ($redir = $this->verificarPermiso($empresa)) return $redir;
        if ($persona->empresa_id !== $empresa->id) abort(404);

        $validated = $request->validated();

        if (!empty($validated['principal'])) {
            $empresa->personasContacto()->where('id', '!=', $persona->id)->update(['principal' => false]);
        }

        $persona->update($validated);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Persona de contacto actualizada correctamente.');
    }

    public function destroy(Empresa $empresa, PersonaContacto $persona): RedirectResponse
    {
        if ($redir = $this->verificarPermiso($empresa)) return $redir;
        if ($persona->empresa_id !== $empresa->id) abort(404);

        $eraPrincipal = $persona->principal;
        $persona->delete();

        // Si era principal y quedan más, asignar la siguiente
        if ($eraPrincipal) {
            $empresa->personasContacto()->first()?->update(['principal' => true]);
        }

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Persona de contacto eliminada.');
    }
}
