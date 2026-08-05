<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionFct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asignaciones_fct';

    protected $fillable = [
        'alumno_id',
        'empresa_id',
        'sede_id',
        'tutor_empresa_id',
        'tutor_ies_id',
        'ciclo_id',
        'curso_academico',
        'numero_curso',
        'fecha_inicio',
        'fecha_fin',
        'num_horas',
        'horario',
        'calendario',
        'estado',
        'motivo_baja',
        'intervalo_email_tutor',
        'ultimo_email_tutor_at',
    ];

    protected $casts = [
        'fecha_inicio'          => 'date',
        'fecha_fin'             => 'date',
        'numero_curso'          => 'integer',
        'num_horas'             => 'integer',
        'intervalo_email_tutor' => 'integer',
        'ultimo_email_tutor_at' => 'datetime',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'sede_id');
    }

    public function tutorEmpresa(): BelongsTo
    {
        return $this->belongsTo(PersonaContacto::class, 'tutor_empresa_id');
    }

    public function tutorIes(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_ies_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
    }

    public function resultadosAprendizaje(): BelongsToMany
    {
        return $this->belongsToMany(
            ResultadoAprendizaje::class,
            'asignacion_ra',
            'asignacion_id',
            'resultado_aprendizaje_id'
        )->withTimestamps();
    }

    public function criteriosEvaluacion(): BelongsToMany
    {
        return $this->belongsToMany(
            CriterioEvaluacion::class,
            'asignacion_ce',
            'asignacion_id',
            'criterio_evaluacion_id'
        )->withTimestamps();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoFct::class, 'asignacion_id');
    }

    public function calendario(): HasMany
    {
        return $this->hasMany(CalendarioAsignacion::class, 'asignacion_id')->orderBy('fecha');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoDiario::class, 'asignacion_id')->orderBy('fecha');
    }

    public function planFormativoDatos(): HasOne
    {
        return $this->hasOne(\App\Models\PlanFormativoDatos::class, 'asignacion_id');
    }

    public function tokenstutor(): HasMany
    {
        return $this->hasMany(TokenTutorEmpresa::class, 'asignacion_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioAsignacion::class, 'asignacion_id')
            ->orderByRaw("FIELD(dia, 'lunes','martes','miercoles','jueves','viernes','sabado','domingo')");
    }

    public function ajustesHoras(): HasMany
    {
        return $this->hasMany(AjusteHorasSemana::class, 'asignacion_id')->orderBy('semana');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActiva($query)
    {
        return $query->where('estado', 'activa');
    }
}
