<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'cif',
        'num_convenio',
        'fecha_firma',
        'creador_id',
        'notas',
    ];

    protected $casts = [
        'fecha_firma' => 'date',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Usuario que creó la empresa (profesor responsable)
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Ciclos formativos que acepta esta empresa
     */
    public function ciclos(): BelongsToMany
    {
        return $this->belongsToMany(CicloFormativo::class, 'empresa_ciclo', 'empresa_id', 'ciclo_id')
            ->withPivot(['acepta_primero', 'acepta_segundo'])
            ->withCasts(['acepta_primero' => 'boolean', 'acepta_segundo' => 'boolean'])
            ->withTimestamps();
    }

    /**
     * Colocaciones de alumnos en esta empresa
     */
    public function colocaciones(): HasMany
    {
        return $this->hasMany(Colocacion::class, 'empresa_id');
    }


    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class)->orderBy('fecha_contacto', 'desc');
    }

    /**
     * Sedes/direcciones de la empresa.
     */
    public function personasContacto(): HasMany
    {
        return $this->hasMany(PersonaContacto::class, 'empresa_id')
            ->orderByDesc('principal')
            ->orderBy('nombre');
    }

    /**
     * Accessor de compatibilidad: devuelve el nombre de la persona principal.
     * Mantiene $empresa->persona_contacto funcional en exports y PDF existentes.
     */
    /**
     * Accessor de compatibilidad: teléfono de la persona principal.
     */
    public function getTelefonoAttribute(): ?string
    {
        if ($this->relationLoaded('personasContacto')) {
            return $this->personasContacto->where('principal', true)->first()?->telefono
                ?? $this->personasContacto->first()?->telefono;
        }
        return $this->personasContacto()->where('principal', true)->first()?->telefono
            ?? $this->personasContacto()->first()?->telefono;
    }

    /**
     * Accessor de compatibilidad: email de la persona principal.
     */
    public function getEmailAttribute(): ?string
    {
        if ($this->relationLoaded('personasContacto')) {
            return $this->personasContacto->where('principal', true)->first()?->email
                ?? $this->personasContacto->first()?->email;
        }
        return $this->personasContacto()->where('principal', true)->first()?->email
            ?? $this->personasContacto()->first()?->email;
    }

    public function getPersonaContactoAttribute(): ?string
    {
        if ($this->relationLoaded('personasContacto')) {
            return $this->personasContacto->where('principal', true)->first()?->nombre
                ?? $this->personasContacto->first()?->nombre;
        }
        return $this->personasContacto()->where('principal', true)->first()?->nombre
            ?? $this->personasContacto()->first()?->nombre;
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(Direccion::class)->orderByDesc('principal')->orderBy('id');
    }

    /**
     * Accessor de compatibilidad: devuelve la dirección formateada de la sede principal.
     * Mantiene $empresa->direccion funcional en exports, PDF y vistas existentes.
     */
    public function getDireccionAttribute(): ?string
    {
        if ($this->relationLoaded('direcciones')) {
            $d = $this->direcciones->where('principal', true)->first()
                ?? $this->direcciones->first();
        } else {
            $d = $this->direcciones()->orderByDesc('principal')->first();
        }

        return $d?->formato_completo ?: null;
    }

    /**
     * Todas las valoraciones de la empresa (por curso académico).
     */
    public function valoraciones(): HasMany
    {
        return $this->hasMany(Valoracion::class)->orderByDesc('curso_academico');
    }

    /**
     * Valoración del curso activo (o del curso indicado).
     * Compatible con $empresa->valoracion existente en vistas.
     */
    public function getValoracionAttribute(): ?Valoracion
    {
        $cursoActivo = \App\Models\Configuracion::cursoActivo();

        if ($this->relationLoaded('valoraciones')) {
            return $this->valoraciones->where('curso_academico', $cursoActivo)->first();
        }

        return $this->valoraciones()->where('curso_academico', $cursoActivo)->first();
    }
    // ACCESSORS - ESTADO DEL CONVENIO
    // =========================================================================

    /**
     * Calcula la fecha de vencimiento del convenio
     */
    public function getFechaVencimientoAttribute(): ?Carbon
    {
        if (!$this->fecha_firma) {
            return null;
        }

        $vigenciaAnos = (int) config('app.convenio.vigencia_anos', 4);
        return $this->fecha_firma->copy()->addYears($vigenciaAnos);
    }

    /**
     * Determina el estado del convenio: activo, por_caducar, caducado, sin_convenio
     */
    public function getEstadoConvenioAttribute(): string
    {
        if (!$this->fecha_firma) {
            return 'sin_convenio';
        }

        $fechaVencimiento = $this->fecha_vencimiento;
        $hoy = Carbon::today();
        $alertaMeses = config('app.convenio.alerta_meses', 6);

        if ($hoy->greaterThan($fechaVencimiento)) {
            return 'caducado';
        }

        if ($hoy->diffInMonths($fechaVencimiento) <= $alertaMeses) {
            return 'por_caducar';
        }

        return 'activo';
    }

    /**
     * Etiqueta del estado para mostrar en la UI
     */
    public function getEstadoEtiquetaAttribute(): string
    {
        return match($this->estado_convenio) {
            'activo' => 'Activo',
            'por_caducar' => 'Próximo a caducar',
            'caducado' => 'Caducado',
            'sin_convenio' => 'Sin convenio',
            default => 'Desconocido',
        };
    }

    /**
     * Color CSS del estado para la UI
     */
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado_convenio) {
            'activo' => 'green',
            'por_caducar' => 'yellow',
            'caducado' => 'red',
            'sin_convenio' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Días restantes hasta el vencimiento
     */
    public function getDiasHastaVencimientoAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }

        return Carbon::today()->diffInDays($this->fecha_vencimiento, false);
    }

    // =========================================================================
    // ACCESSORS - ESTADÍSTICAS
    // =========================================================================

    /**
     * Total de alumnos colocados en esta empresa (histórico)
     */
    public function getTotalAlumnosAttribute(): int
    {
        // P3: usar colección en memoria si ya está cargada, evita query extra
        if ($this->relationLoaded('colocaciones')) {
            return $this->colocaciones->sum('num_alumnos');
        }
        return $this->colocaciones()->sum('num_alumnos');
    }

    /**
     * Total de horas de prácticas en esta empresa (histórico)
     */
    public function getTotalHorasAttribute(): int
    {
        if ($this->relationLoaded('colocaciones')) {
            return $this->colocaciones->sum('num_horas');
        }
        return $this->colocaciones()->sum('num_horas');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Filtrar por estado de convenio
     */
    public function scopeEstadoConvenio($query, string $estado)
    {
        $hoy = Carbon::today();
        $vigenciaAnos = config('app.convenio.vigencia_anos', 4);
        $alertaMeses = config('app.convenio.alerta_meses', 6);
        
        return match($estado) {
            'activo' => $query->whereNotNull('fecha_firma')
                ->whereRaw("DATE_ADD(fecha_firma, INTERVAL ? YEAR) > ?", [$vigenciaAnos, $hoy])
                ->whereRaw("DATE_ADD(fecha_firma, INTERVAL ? YEAR) > DATE_ADD(?, INTERVAL ? MONTH)", [$vigenciaAnos, $hoy, $alertaMeses]),
            
            'por_caducar' => $query->whereNotNull('fecha_firma')
                ->whereRaw("DATE_ADD(fecha_firma, INTERVAL ? YEAR) > ?", [$vigenciaAnos, $hoy])
                ->whereRaw("DATE_ADD(fecha_firma, INTERVAL ? YEAR) <= DATE_ADD(?, INTERVAL ? MONTH)", [$vigenciaAnos, $hoy, $alertaMeses]),
            
            'caducado' => $query->whereNotNull('fecha_firma')
                ->whereRaw("DATE_ADD(fecha_firma, INTERVAL ? YEAR) <= ?", [$vigenciaAnos, $hoy]),
            
            'sin_convenio' => $query->whereNull('fecha_firma'),
            
            default => $query,
        };
    }

    /**
     * Filtrar por ciclo formativo
     */
    public function scopeCiclo($query, int $cicloId)
    {
        return $query->whereHas('ciclos', function ($q) use ($cicloId) {
            $q->where('ciclos_formativos.id', $cicloId);
        });
    }

    /**
     * Filtrar por curso que acepta (1º o 2º)
     */
    public function scopeAceptaCurso($query, int $cicloId, int $numeroCurso)
    {
        $campo = $numeroCurso === 1 ? 'acepta_primero' : 'acepta_segundo';
        
        return $query->whereHas('ciclos', function ($q) use ($cicloId, $campo) {
            $q->where('ciclos_formativos.id', $cicloId)
              ->where("empresa_ciclo.{$campo}", true);
        });
    }

    /**
     * Búsqueda por texto
     */
    public function scopeBuscar($query, string $termino)
{
    $termino = "%{$termino}%";
    
    return $query->where(function ($q) use ($termino) {
        $q->where('nombre', 'like', $termino)
          ->orWhere('cif', 'like', $termino)
          ->orWhereHas('personasContacto', function ($qp) use ($termino) {
              $qp->where('nombre', 'like', $termino)
                 ->orWhere('email', 'like', $termino);
          })
          ->orWhereHas('creador', function ($q2) use ($termino) {
              $q2->where('nombre', 'like', $termino);
          });
    });
}


    /**
     * Ordenar por estado de convenio (urgentes primero)
     */
    public function scopeOrdenarPorUrgencia($query)
    {
        $hoy = Carbon::today()->toDateString();
        $vigenciaAnos = config('app.convenio.vigencia_anos', 4);
        
        return $query->orderByRaw("
            CASE 
                WHEN fecha_firma IS NULL THEN 3
                WHEN DATE_ADD(fecha_firma, INTERVAL ? YEAR) <= ? THEN 0
                ELSE 2
            END ASC,
            DATE_ADD(fecha_firma, INTERVAL ? YEAR) ASC
        ", [$vigenciaAnos, $hoy, $vigenciaAnos]);
    }

    // =========================================================================
    // MÉTODOS
    // =========================================================================

    /**
     * Sincroniza los ciclos formativos de la empresa
     */
    public function sincronizarCiclos(array $ciclosData): void
    {
        $syncData = [];
        
        foreach ($ciclosData as $cicloId => $datos) {
            $syncData[$cicloId] = [
                'acepta_primero' => $datos['acepta_primero'] ?? false,
                'acepta_segundo' => $datos['acepta_segundo'] ?? true,
            ];
        }
        
        $this->ciclos()->sync($syncData);
    }

    /**
     * Obtiene las colocaciones agrupadas por curso académico
     */
    public function colocacionesPorCurso(): array
    {
        return $this->colocaciones()
            ->with('ciclo')
            ->orderBy('curso_academico', 'desc')
            ->orderBy('ciclo_id')
            ->get()
            ->groupBy('curso_academico')
            ->toArray();
    }
}
