<?php

namespace App\Providers;

use App\Models\AsignacionFct;
use App\Models\Empresa;
use App\Models\User;
use App\Policies\AsignacionPolicy;
use App\Policies\EmpresaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Empresa::class => EmpresaPolicy::class,
        AsignacionFct::class => AsignacionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate para verificar si es administrador
        Gate::define('admin', function (User $user) {
            return $user->esAdmin();
        });

        // Gate para verificar si es responsable de ciclo o admin
        Gate::define('responsable', function (User $user) {
            return $user->esAdmin() || $user->esResponsableCiclo();
        });

        // Gate para gestion FFE (Ciclos, Alumnos, Curriculum, Calendario FFE): admin o responsable_ffe
        Gate::define('gestionarFfe', function (User $user) {
            return $user->esAdmin() || $user->esResponsableFFE();
        });

        // Directivas Blade personalizadas
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->esAdmin();
        });

        Blade::if('responsable', function () {
            return auth()->check() && (auth()->user()->esAdmin() || auth()->user()->esResponsableCiclo());
        });

        Blade::if('profesor', function () {
            return auth()->check() && auth()->user()->esProfesor();
        });
    }
}
