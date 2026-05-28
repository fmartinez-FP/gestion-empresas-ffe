<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Models\CicloFormativo;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BuscadorEmpresas extends Component
{
    use WithPagination;

    // Filtros
    public string $buscar = '';
    public string $ciclo = '';
    public string $curso = '';
    public string $estado = '';
    
    // Ordenación
    public string $ordenarPor = 'nombre';
    public string $ordenDir = 'asc';

    // Actualizar en tiempo real
    protected $queryString = [
        'buscar' => ['except' => ''],
        'ciclo' => ['except' => ''],
        'curso' => ['except' => ''],
        'estado' => ['except' => ''],
        'ordenarPor' => ['except' => 'nombre'],
        'ordenDir' => ['except' => 'asc'],
    ];

    // Resetear paginación cuando cambian los filtros
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingCiclo()
    {
        $this->resetPage();
    }

    public function updatingCurso()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    // Ordenar por columna
    public function ordenar(string $columna)
    {
        if ($this->ordenarPor === $columna) {
            $this->ordenDir = $this->ordenDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $columna;
            $this->ordenDir = 'asc';
        }
    }

    // Limpiar filtros
    public function limpiarFiltros()
    {
        $this->reset(['buscar', 'ciclo', 'curso', 'estado']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Empresa::with(['creador', 'ciclos']);

        // Búsqueda por texto
        if ($this->buscar) {
            $query->buscar($this->buscar);
        }

        // Filtro por ciclo
        if ($this->ciclo) {
            $query->ciclo($this->ciclo);
        }

        // Filtro por curso que acepta
        if ($this->ciclo && $this->curso) {
            $query->aceptaCurso($this->ciclo, (int) $this->curso);
        }

        // Filtro por estado
        if ($this->estado) {
            $query->estadoConvenio($this->estado);
        }

        // Ordenación
        if ($this->ordenarPor === 'estado') {
            $query->ordenarPorUrgencia();
        } else {
            $query->orderBy($this->ordenarPor, $this->ordenDir);
        }

        $empresas = $query->paginate(15);
        $ciclos = CicloFormativo::activos()->orderBy('nivel')->orderBy('nombre')->get();

        // Contadores respetan buscar y ciclo pero no el filtro de estado
        $baseContadores = Empresa::query();
        if ($this->buscar) {
            $baseContadores->buscar($this->buscar);
        }
        if ($this->ciclo) {
            $baseContadores->ciclo($this->ciclo);
            if ($this->curso) {
                $baseContadores->aceptaCurso($this->ciclo, (int) $this->curso);
            }
        }
        $vigenciaAnos = (int) config('app.convenio.vigencia_anos', 4);
        $alertaMeses  = (int) config('app.convenio.alerta_meses', 6);
        $hoy          = Carbon::today()->toDateString();

        // P2: 4 COUNT queries → 1 query con agregados condicionales
        $counts = (clone $baseContadores)->selectRaw("
            COUNT(*) as total,
            SUM(CASE
                WHEN fecha_firma IS NOT NULL
                AND DATE_ADD(fecha_firma, INTERVAL ? YEAR) > ?
                AND DATE_ADD(fecha_firma, INTERVAL ? YEAR) > DATE_ADD(?, INTERVAL ? MONTH)
                THEN 1 ELSE 0 END) as activo,
            SUM(CASE
                WHEN fecha_firma IS NOT NULL
                AND DATE_ADD(fecha_firma, INTERVAL ? YEAR) > ?
                AND DATE_ADD(fecha_firma, INTERVAL ? YEAR) <= DATE_ADD(?, INTERVAL ? MONTH)
                THEN 1 ELSE 0 END) as por_caducar,
            SUM(CASE
                WHEN fecha_firma IS NOT NULL
                AND DATE_ADD(fecha_firma, INTERVAL ? YEAR) <= ?
                THEN 1 ELSE 0 END) as caducado
        ", [
            $vigenciaAnos, $hoy, $vigenciaAnos, $hoy, $alertaMeses,
            $vigenciaAnos, $hoy, $vigenciaAnos, $hoy, $alertaMeses,
            $vigenciaAnos, $hoy,
        ])->first();

        $contadores = [
            'total'       => (int) $counts->total,
            'activo'      => (int) $counts->activo,
            'por_caducar' => (int) $counts->por_caducar,
            'caducado'    => (int) $counts->caducado,
        ];

        return view('livewire.buscador-empresas', compact('empresas', 'ciclos', 'contadores'));
    }
}
