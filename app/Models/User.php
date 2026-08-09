<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements CanResetPassword, LdapAuthenticatable
{
    use HasFactory, Notifiable, CanResetPasswordTrait, AuthenticatesWithLdap;

    protected $fillable = [
        'username', 'nombre', 'email', 'password',
        'rol', 'ciclo_id', 'activo', 'preferencias',
        'guid', 'domain', 'password_change_required',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password'                 => 'hashed',
        'activo'                   => 'boolean',
        'preferencias'             => 'array',
        'password_change_required' => 'boolean',
    ];

    /**
     * Al crear un usuario desde LDAP por primera vez:
     * - password: placeholder aleatorio (nunca se usa, auth es LDAP)
     * - rol: profesor por defecto, EXCEPTO el username 'admin' (administrador
     *   fijo de IES Pacifico), que siempre nace como admin sin promocion manual.
     * - activo: true
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->password)) {
                $user->password = bcrypt(\Illuminate\Support\Str::random(32));
            }
            if (empty($user->rol)) {
                $user->rol = $user->username === 'admin' ? 'admin' : 'profesor';
            }
            if (!isset($user->activo)) {
                $user->activo = true;
            }
        });
    }

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
    }

    public function ciclos(): BelongsToMany
    {
        return $this->belongsToMany(CicloFormativo::class, 'ciclo_user', 'user_id', 'ciclo_id')
            ->withTimestamps();
    }

    /**
     * Grupos que este profesor tutoriza en el curso academico ACTIVO (no
     * historico). wherePivot() restringe tanto la lectura como lo que sync()
     * considera "actualmente vinculado" - sync() solo desvincula filas que
     * matchean las restricciones wherePivot de la relacion, asi que asignar
     * grupos para el curso vigente nunca toca (ni borra) filas de cursos
     * anteriores. Verificado en GrupoTutorTest::sync_preserva_historico().
     */
    public function gruposTutor(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'profesor_tutor', 'user_id', 'grupo_id')
            ->wherePivot('curso_academico', Configuracion::cursoActivo())
            ->withPivot('curso_academico')
            ->withTimestamps();
    }

    public function empresasCreadas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'creador_id');
    }

    public function colocacionesRegistradas(): HasMany
    {
        return $this->hasMany(Colocacion::class, 'registrado_por_id');
    }

    // =========================================================================
    // MÉTODOS DE ROL
    // =========================================================================

    public function alumno(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Alumno::class, 'user_id');
    }

    public function esAdmin(): bool           { return $this->rol === 'admin'; }
    public function esAlumno(): bool          { return $this->rol === 'alumno'; }
    public function esTutorEmpresa(): bool    { return $this->rol === 'tutor_empresa'; }
    public function esResponsableFFE(): bool  { return $this->rol === 'responsable_ffe'; }
    public function esResponsableCiclo(): bool{ return $this->rol === 'responsable_ciclo'; }
    public function esProfesor(): bool        { return $this->rol === 'profesor'; }

    public function sincronizarCiclos(array $cicloIds): void
    {
        $this->ciclos()->sync($cicloIds);
        $this->update(['ciclo_id' => !empty($cicloIds) ? $cicloIds[0] : null]);
    }

    /**
     * Sincroniza los grupos que este profesor tutoriza en el curso activo.
     * No toca ciclo_id/ciclo_user (son de responsable_ciclo, tabla distinta).
     */
    public function sincronizarGruposTutor(array $grupoIds): void
    {
        $this->gruposTutor()->sync(
            collect($grupoIds)->mapWithKeys(fn ($id) => [
                $id => ['curso_academico' => Configuracion::cursoActivo()],
            ])
        );
    }

    // =========================================================================
    // PREFERENCIAS
    // =========================================================================

    public function getPreferencia(string $key, $default = null)
    {
        return ($this->preferencias ?? [])[$key] ?? $default;
    }

    public function setPreferencia(string $key, $value): void
    {
        $preferencias = $this->preferencias ?? [];
        $preferencias[$key] = $value;
        $this->update(['preferencias' => $preferencias]);
    }

    public function modoOscuro(): bool
    {
        return $this->getPreferencia('modo_oscuro', false);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActivos($query)  { return $query->where('activo', true); }
    public function scopeRol($query, string $rol) { return $query->where('rol', $rol); }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getNombreRolAttribute(): string
    {
        return match($this->rol) {
            'admin'            => 'Administrador',
            'responsable_ffe'  => 'Responsable FFE',
            'responsable_ciclo'=> 'Responsable de Ciclo',
            'profesor'         => 'Profesor',
            default            => $this->rol,
        };
    }

    public function getNombresCiclosAttribute(): string
    {
        if (!$this->esResponsableCiclo()) return '';
        return $this->ciclos->pluck('codigo')->implode(', ');
    }

}
