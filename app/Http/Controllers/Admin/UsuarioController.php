<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CicloFormativo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('ciclos');

        if ($request->filled('buscar')) {
            $termino = "%{$request->buscar}%";
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', $termino)
                  ->orWhere('email', 'like', $termino)
                  ->orWhere('username', 'like', $termino);
            });
        }

        if ($request->filled('rol')) {
            $query->rol($request->rol);
        }

        $usuarios = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function show(User $usuario): View
    {
        $usuario->load(['ciclos', 'empresasCreadas', 'colocacionesRegistradas']);
        return view('admin.usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $usuario->load('ciclos');
        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('usuario', 'ciclos'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id() && $usuario->esAdmin()) {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        $validated = $request->validate([
            'rol'      => 'required|in:admin,responsable_ffe,responsable_ciclo,profesor',
            'ciclos'   => 'nullable|array',
            'ciclos.*' => 'exists:ciclos_formativos,id',
        ], [
            'rol.required' => 'El rol es obligatorio.',
        ]);

        if ($validated['rol'] === 'responsable_ciclo' && empty($request->ciclos)) {
            return back()
                ->withErrors(['ciclos' => 'Debes seleccionar al menos un ciclo para el responsable.'])
                ->withInput();
        }

        $usuario->update(['rol' => $validated['rol']]);

        if ($validated['rol'] === 'responsable_ciclo') {
            $usuario->sincronizarCiclos($request->ciclos ?? []);
        } else {
            $usuario->ciclos()->detach();
            $usuario->update(['ciclo_id' => null]);
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Rol actualizado correctamente.');
    }
}
