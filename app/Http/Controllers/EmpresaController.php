<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\CicloFormativo;
use App\Models\User;
use App\Models\Auditoria;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;

class EmpresaController extends Controller
{
    /**
     * Listado de empresas (el filtrado lo gestiona Livewire)
     */
    public function index(): View
    {
        return view('empresas.index');
    }

    /**
     * Formulario de creación
     */
    public function create(): View
    {
        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        return view('empresas.create', compact('ciclos'));
    }

    /**
     * Guardar nueva empresa
     */
    public function store(StoreEmpresaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $empresa = Empresa::create([
            ...$validated,
            'creador_id' => auth()->id(),
        ]);

        // Crear persona de contacto inicial si se proporcionó
        if (!empty($validated['contacto_nombre'])) {
            $empresa->personasContacto()->create([
                'nombre'    => $validated['contacto_nombre'],
                'cargo'     => $validated['contacto_cargo'] ?? null,
                'telefono'  => $validated['contacto_telefono'] ?? null,
                'email'     => $validated['contacto_email'] ?? null,
                'principal' => true,
            ]);
        }

        // Sincronizar ciclos
        if ($request->has('ciclos')) {
            $ciclosData = [];
            foreach ($request->ciclos as $cicloId => $datos) {
                if (isset($datos['seleccionado']) && $datos['seleccionado']) {
                    $ciclosData[$cicloId] = [
                        'acepta_primero' => isset($datos['acepta_primero']) ? true : false,
			'acepta_segundo' => isset($datos['acepta_segundo']) ? true : false,
                    ];
                }
            }
            $empresa->sincronizarCiclos($ciclosData);
        }

        // Registrar auditoría
        Auditoria::registrarCreacion($empresa, "Empresa creada: {$empresa->nombre}");

        // Notificar a responsables_ciclo y responsable_ffe
        NotificacionService::notificarResponsablesCiclo($empresa, 'empresa_creada', "Nueva empresa: {$empresa->nombre}", route('empresas.show', $empresa));
        NotificacionService::notificarResponsablesFFE($empresa, 'empresa_creada', "Nueva empresa: {$empresa->nombre}", route('empresas.show', $empresa));

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', 'Empresa creada correctamente.');
    }

    /**
     * Ver detalle de empresa
     */
    public function show(Empresa $empresa): View
    {
        $empresa->load(['creador', 'ciclos', 'colocaciones.ciclo', 'colocaciones.registradoPor', 'valoraciones.valoradoPor']);

        if (auth()->user()->can('verPersonasContacto', $empresa)) {
            $empresa->load('personasContacto');
        }

        $auditorias = auth()->user()->can('verAuditoria', $empresa)
            ? Auditoria::where('modelo', 'Empresa')
                ->where('modelo_id', $empresa->id)
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return view('empresas.show', compact('empresa', 'auditorias'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Empresa $empresa): View|RedirectResponse
    {
        // Verificar permisos
        if (!auth()->user()->can('update', $empresa)) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para editar esta empresa.');
        }

        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();
        $empresa->load(['ciclos', 'personasContacto']);
        
        // Lista de profesores para reasignación (solo admin y responsables)
        $profesores = null;
        if (auth()->user()->esAdmin() || auth()->user()->esResponsableFFE() || auth()->user()->esResponsableCiclo()) {
            $profesores = User::where('activo', true)
                ->orderBy('nombre')
                ->get();
        }
        
        return view('empresas.edit', compact('empresa', 'ciclos', 'profesores'));
    }

    /**
     * Actualizar empresa
     */
    public function update(UpdateEmpresaRequest $request, Empresa $empresa): RedirectResponse
    {
        // Verificar permisos
        if (!auth()->user()->can('update', $empresa)) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para editar esta empresa.');
        }

        $validated = $request->validated();

        // Guardar datos anteriores para auditoría
        $datosAnteriores = $empresa->toArray();

        // Separar creador_id del array de update masivo para controlarlo explícitamente
        $nuevoCreadorId = $validated['creador_id'] ?? null;
        unset($validated['creador_id']);

        // Reasignación del profesor responsable
        if ($request->filled('creador_id') && $nuevoCreadorId) {
            $user = auth()->user();
            $puedeReasignar = false;

            if ($user->esAdmin() || $user->esResponsableFFE()) {
                // Admin y responsable FFE: sin restricción de ciclo
                $puedeReasignar = true;
            } elseif ($user->esResponsableCiclo()) {
                // Responsable de ciclo: solo si la empresa comparte
                // al menos un ciclo formativo con los del usuario
                $ciclosIds = $user->ciclos()->pluck('ciclos_formativos.id')->toArray();
                $puedeReasignar = count($ciclosIds) > 0
                    && $empresa->ciclos()
                        ->whereIn('ciclos_formativos.id', $ciclosIds)
                        ->exists();
            }

            if ($puedeReasignar) {
                $empresa->creador_id = $nuevoCreadorId;
            }
        }

        $empresa->update($validated);

        // Sincronizar ciclos (solo si el formulario incluía la sección de ciclos)
        if ($request->has('ciclos_enviados')) {
            $ciclosData = [];
            foreach ($request->input('ciclos', []) as $cicloId => $datos) {
                if (isset($datos['seleccionado']) && $datos['seleccionado']) {
                    $ciclosData[$cicloId] = [
                        'acepta_primero' => isset($datos['acepta_primero']) ? true : false,
                        'acepta_segundo' => isset($datos['acepta_segundo']) ? true : false,
                    ];
                }
            }
            $empresa->sincronizarCiclos($ciclosData);
        }

        // Registrar auditoría
        Auditoria::registrarActualizacion($empresa, $datosAnteriores, "Empresa actualizada: {$empresa->nombre}");

        // Notificar a responsables_ciclo
        NotificacionService::notificarResponsablesCiclo($empresa, 'empresa_editada', "Empresa actualizada: {$empresa->nombre}", route('empresas.show', $empresa));

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * Eliminar empresa (solo admin)
     */
    public function destroy(Empresa $empresa): RedirectResponse
    {
        if (!auth()->user()->can('delete', $empresa)) {
            return redirect()
                ->route('empresas.index')
                ->with('error', 'Solo los administradores pueden eliminar empresas.');
        }

        $nombre = $empresa->nombre;
        
        // Registrar auditoría antes de eliminar
        Auditoria::registrarEliminacion($empresa, "Empresa eliminada: {$nombre}");
        
        $empresa->delete();

        return redirect()
            ->route('empresas.index')
            ->with('success', "Empresa '{$nombre}' eliminada correctamente.");
    }

    /**
     * Renovar convenio (actualizar fecha_firma a hoy)
     */
    public function renovar(Empresa $empresa): RedirectResponse
    {
        // Verificar permisos
        if (!auth()->user()->can('update', $empresa)) {
            return redirect()
                ->route('empresas.show', $empresa)
                ->with('error', 'No tienes permisos para renovar el convenio de esta empresa.');
        }

        $datosAnteriores = $empresa->toArray();

        $empresa->update([
            'fecha_firma' => now(),
        ]);

        Auditoria::registrarActualizacion($empresa, $datosAnteriores, "Convenio renovado: {$empresa->nombre}");

        // Notificar a responsables_ciclo
        NotificacionService::notificarResponsablesCiclo($empresa, 'convenio_renovado', "Convenio renovado: {$empresa->nombre}", route('empresas.show', $empresa));

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('success', 'Convenio renovado correctamente. Nueva vigencia hasta ' . $empresa->fecha_vencimiento->format('d/m/Y') . '.');
    }

    /**
     * Duplicar empresa (crear copia sin colocaciones)
     */
    public function duplicar(Empresa $empresa): RedirectResponse
    {
        $empresa->load('ciclos');

        // Crear copia
        $nuevaEmpresa = Empresa::create([
            'nombre' => $empresa->nombre . ' (copia)',
            'cif' => '', // El usuario deberá introducir uno nuevo
            'direccion' => $empresa->direccion,
            'num_convenio' => null,
            'fecha_firma' => null,
            'notas' => $empresa->notas,
            'creador_id' => auth()->id(),
        ]);

        // Copiar ciclos
        $ciclosData = [];
        foreach ($empresa->ciclos as $ciclo) {
            $ciclosData[$ciclo->id] = [
                'acepta_primero' => $ciclo->pivot->acepta_primero,
                'acepta_segundo' => $ciclo->pivot->acepta_segundo,
            ];
        }
        $nuevaEmpresa->sincronizarCiclos($ciclosData);

        // Copiar personas de contacto
        foreach ($empresa->personasContacto as $persona) {
            $nuevaEmpresa->personasContacto()->create([
                'nombre'    => $persona->nombre,
                'cargo'     => $persona->cargo,
                'telefono'  => $persona->telefono,
                'email'     => $persona->email,
                'notas'     => $persona->notas,
                'principal' => $persona->principal,
            ]);
        }

        Auditoria::registrarCreacion($nuevaEmpresa, "Empresa duplicada desde: {$empresa->nombre}");

        return redirect()
            ->route('empresas.edit', $nuevaEmpresa)
            ->with('info', 'Empresa duplicada. Por favor, actualiza el nombre y el CIF antes de guardar.');
    }
}
