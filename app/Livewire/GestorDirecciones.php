<?php

namespace App\Livewire;

use App\Models\Direccion;
use App\Models\Empresa;
use App\Services\GeocodingService;
use Livewire\Component;

class GestorDirecciones extends Component
{
    public Empresa $empresa;
    public bool $mostrarFormulario = false;
    public ?int $editandoId = null;

    public string $tipo_via = 'Calle';
    public string $nombre_via = '';
    public string $numero = '';
    public string $codigo_postal = '';
    public string $municipio = '';

    protected function rules(): array
    {
        return [
            'tipo_via'      => 'nullable|string|max:50',
            'nombre_via'    => 'required|string|max:255',
            'numero'        => 'nullable|string|max:20',
            'codigo_postal' => 'nullable|digits:5',
            'municipio'     => 'nullable|string|max:100',
        ];
    }

    protected $messages = [
        'nombre_via.required'  => 'El nombre de la vía es obligatorio.',
        'codigo_postal.digits' => 'El código postal debe tener 5 dígitos.',
    ];

    public function agregar(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $dir = Direccion::findOrFail($id);
        abort_unless($dir->empresa_id === $this->empresa->id, 403);

        $this->editandoId    = $id;
        $this->tipo_via      = $dir->tipo_via ?? 'Calle';
        $this->nombre_via    = $dir->nombre_via;
        $this->numero        = $dir->numero ?? '';
        $this->codigo_postal = $dir->codigo_postal ?? '';
        $this->municipio     = $dir->municipio ?? '';
        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate();

        $datos = [
            'tipo_via'      => $this->tipo_via ?: null,
            'nombre_via'    => $this->nombre_via,
            'numero'        => $this->numero ?: null,
            'codigo_postal' => $this->codigo_postal ?: null,
            'municipio'     => $this->municipio ?: null,
        ];

        if ($this->editandoId) {
            $dir = Direccion::findOrFail($this->editandoId);
            abort_unless($dir->empresa_id === $this->empresa->id, 403);
            $dir->update($datos);
        } else {
            $esPrimera = $this->empresa->direcciones()->count() === 0;
            $dir = $this->empresa->direcciones()->create(
                array_merge($datos, ['principal' => $esPrimera])
            );
        }

        $geocodificada = GeocodingService::geocodificar($dir);

        $this->resetFormulario();
        $this->mostrarFormulario = false;

        session()->flash(
            $geocodificada ? 'sede_success' : 'sede_warning',
            $geocodificada
                ? 'Dirección guardada y localizada en el mapa.'
                : 'Dirección guardada. No se pudo localizar automáticamente (completa todos los campos).'
        );
    }

    public function eliminar(int $id): void
    {
        $dir = Direccion::findOrFail($id);
        abort_unless($dir->empresa_id === $this->empresa->id, 403);

        $eraPrincipal = $dir->principal;
        $dir->delete();

        if ($eraPrincipal) {
            $this->empresa->direcciones()->first()?->update(['principal' => true]);
        }
    }

    public function marcarPrincipal(int $id): void
    {
        $dir = Direccion::findOrFail($id);
        abort_unless($dir->empresa_id === $this->empresa->id, 403);

        $this->empresa->direcciones()->update(['principal' => false]);
        $dir->update(['principal' => true]);
    }

    public function cancelar(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    private function resetFormulario(): void
    {
        $this->tipo_via = 'Calle';
        $this->nombre_via = '';
        $this->numero = '';
        $this->codigo_postal = '';
        $this->municipio = '';
        $this->editandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.gestor-direcciones', [
            'direcciones' => $this->empresa->direcciones()->get(),
            'tiposVia'    => Direccion::TIPOS_VIA,
            'puedeEditar' => auth()->user()->can('update', $this->empresa),
        ]);
    }
}
