<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Empresa;
use Illuminate\Http\Request;
use App\Http\Requests\StoreContactoRequest;
use App\Http\Requests\UpdateContactoRequest;
use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContactoController extends Controller
{
    use AuthorizesRequests;
    public function create(Empresa $empresa)
    {
        // Solo el creador de la empresa puede añadir contactos
        $this->authorize('update', $empresa);

        return view('contactos.create', compact('empresa'));
    }

    public function store(StoreContactoRequest $request, Empresa $empresa)
    {
        $this->authorize('update', $empresa);

        $validated = $request->validated();

        $contacto = new Contacto($validated);
        $contacto->empresa_id = $empresa->id;
        $contacto->registrado_por_id = auth()->id();

        // Manejar archivo adjunto
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $ruta = $archivo->store('contactos/' . $empresa->id, 'private');
            $contacto->archivo_adjunto = $ruta;
            $contacto->archivo_nombre = $nombreOriginal;
        }

        $contacto->save();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Contacto registrado correctamente.');
    }

    public function edit(Empresa $empresa, Contacto $contacto)
    {
        $this->authorize('update', $empresa);

        if ($contacto->empresa_id !== $empresa->id) {
            abort(404);
        }

        return view('contactos.edit', compact('empresa', 'contacto'));
    }

    public function update(UpdateContactoRequest $request, Empresa $empresa, Contacto $contacto)
    {
        $this->authorize('update', $empresa);

        if ($contacto->empresa_id !== $empresa->id) {
            abort(404);
        }

        $validated = $request->validated();

        $contacto->fill($validated);

        // Eliminar archivo si se solicita
        if ($request->boolean('eliminar_archivo') && $contacto->archivo_adjunto) {
            Storage::disk('private')->delete($contacto->archivo_adjunto);
            $contacto->archivo_adjunto = null;
            $contacto->archivo_nombre = null;
        }

        // Nuevo archivo
        if ($request->hasFile('archivo')) {
            // Eliminar anterior
            if ($contacto->archivo_adjunto) {
                Storage::disk('private')->delete($contacto->archivo_adjunto);
            }
            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $ruta = $archivo->store('contactos/' . $empresa->id, 'private');
            $contacto->archivo_adjunto = $ruta;
            $contacto->archivo_nombre = $nombreOriginal;
        }

        $contacto->save();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Contacto actualizado correctamente.');
    }

    public function destroy(Empresa $empresa, Contacto $contacto)
    {
        $this->authorize('update', $empresa);

        if ($contacto->empresa_id !== $empresa->id) {
            abort(404);
        }

        // Eliminar archivo adjunto
        if ($contacto->archivo_adjunto) {
            Storage::disk('private')->delete($contacto->archivo_adjunto);
        }

        $contacto->delete();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Contacto eliminado correctamente.');
    }

    public function descargarArchivo(Empresa $empresa, Contacto $contacto)
    {
        $this->authorize('update', $empresa);

        if ($contacto->empresa_id !== $empresa->id || !$contacto->archivo_adjunto) {
            abort(404);
        }

        return Storage::disk('private')->download($contacto->archivo_adjunto, $contacto->archivo_nombre);
    }
}
