<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Colocacion extends Model
{
    use HasFactory;

    protected $table = 'colocaciones';

    protected $fillable = [
        'empresa_id',
        'ciclo_id',
        'registrado_por_id',
        'curso_academico',
        'numero_curso',
        'num_alumnos',
        'num_horas',
        'observaciones',
    ];

    protected $casts = [
        'numero_curso' => 'integer',
        'num_alumnos' => 'integer',
        'num_horas' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getCursoEtiquetaAttribute(): string
    {
        return $this->numero_curso . 'º';
    }

    public function getDescripcionAttribute(): string
    {
        return "{$this->num_alumnos} alumnos ({$this->num_horas}h) - {$this->ciclo->codigo} {$this->curso_etiqueta}";
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeCursoAcademico($query, string $curso)
    {
        return $query->where('curso_academico', $curso);
    }

    public function scopeCiclo($query, int $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    public function scopeNumeroCurso($query, int $numero)
    {
        return $query->where('numero_curso', $numero);
    }

    public function scopeUltimosAnos($query, int $anos = 5)
    {
        $cursoActual = Configuracion::cursoActivo();
        $anoInicio = (int) substr($cursoActual, 0, 4) - $anos + 1;
        
        return $query->where('curso_academico', '>=', $anoInicio . '-' . ($anoInicio + 1));
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS
    // =========================================================================


    /**
     * Calcula el curso académico actual basándose en la fecha (sep-jun).
     */
    public static function obtenerCursoActual(): string
    {
        $now = \Carbon\Carbon::now();
        $year = $now->month >= 9 ? $now->year : $now->year - 1;
        return $year . '-' . ($year + 1);
    }

    /**
     * Genera lista de cursos académicos (actual + $anos anteriores).
     * $numeroCurso es opcional para compatibilidad futura.
     */
    public static function generarListaCursos(int $anos = 3, int $numeroCurso = null): array
    {
        $actual = self::obtenerCursoActual();
        [$startYear] = explode('-', $actual);
        $startYear = (int) $startYear;

        $cursos = [];
        for ($i = 0; $i >= -$anos; $i--) {
            $year = $startYear + $i;
            $cursos[] = $year . '-' . ($year + 1);
        }

        return $cursos;
    }

    /**
     * Obtiene el curso activo (desde configuración)
     */
    public static function cursoActivo(): string
    {
        return Configuracion::cursoActivo();
    }

    /**
     * Genera lista de cursos para el histórico (todos los que tienen datos + activo)
     */
    public static function cursosConDatos(): array
    {
        $cursos = self::distinct()
            ->pluck('curso_academico')
            ->toArray();
        
        // Añadir el curso activo si no está
        $activo = self::cursoActivo();
        if (!in_array($activo, $cursos)) {
            $cursos[] = $activo;
        }
        
        // Ordenar descendente
        rsort($cursos);
        
        return $cursos;
    }

    /**
     * Estadísticas por ciclo y curso académico
     */
    public static function estadisticasPorCiclo(string $cursoAcademico = null): array
    {
        $query = self::query()
            ->selectRaw('ciclo_id, curso_academico, numero_curso, SUM(num_alumnos) as total_alumnos, SUM(num_horas) as total_horas, COUNT(*) as num_envios')
            ->groupBy('ciclo_id', 'curso_academico', 'numero_curso')
            ->with('ciclo');

        if ($cursoAcademico) {
            $query->where('curso_academico', $cursoAcademico);
        }

        return $query->orderBy('curso_academico', 'desc')
            ->orderBy('ciclo_id')
            ->orderBy('numero_curso')
            ->get()
            ->groupBy('curso_academico')
            ->toArray();
    }
}
