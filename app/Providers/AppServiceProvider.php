<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Models\Notificacion;
use App\Contracts\PdfGeneratorInterface;
use App\Services\DompdfAdapter;
use App\Policies\AlumnoPolicy;
use App\Policies\AsignacionPolicy;
use App\Policies\CurriculumPolicy;
use App\Policies\DocumentoFctPolicy;
use App\Policies\SeguimientoDiarioPolicy;
use Illuminate\Support\Facades\Gate;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding interfaz PDF → adaptador DomPDF (swappable en tests)
        $this->app->bind(PdfGeneratorInterface::class, DompdfAdapter::class);
    }
    public function boot(): void
    {
        Carbon::setLocale('es');

        // Gates para Alumno (abilities sin modelo, delegadas a AlumnoPolicy)
        Gate::define('crearAlumno',    [AlumnoPolicy::class, 'crearAlumno']);
        Gate::define('editarAlumno',   [AlumnoPolicy::class, 'editarAlumno']);
        Gate::define('eliminarAlumno', [AlumnoPolicy::class, 'eliminarAlumno']);
        Gate::define('verArchivo',     [AlumnoPolicy::class, 'verArchivo']);
        Gate::define('importarAlumnos',[AlumnoPolicy::class, 'importarAlumnos']);

        // Gates para Asignaciones FFE
        Gate::define('crearAsignacion',   [AsignacionPolicy::class, 'crearAsignacion']);
        Gate::define('verAsignacion',     [AsignacionPolicy::class, 'verAsignacion']);
        Gate::define('editarAsignacion',  [AsignacionPolicy::class, 'editarAsignacion']);
        Gate::define('cancelarAsignacion',[AsignacionPolicy::class, 'cancelarAsignacion']);
        Gate::define('ajustarHorasSemana',[AsignacionPolicy::class, 'ajustarHorasSemana']);
        Gate::define('marcarDiaNoTrabajado',[AsignacionPolicy::class, 'marcarDiaNoTrabajado']);

        // Gates para Currículum (módulos, RA, CE, elegibles)
        Gate::define('verCurriculum',      [CurriculumPolicy::class, 'verCurriculum']);
        Gate::define('gestionarCurriculum',[CurriculumPolicy::class, 'gestionarCurriculum']);
        Gate::define('gestionarElegibles', [CurriculumPolicy::class, 'gestionarElegibles']);

        // Gates para Documentos FCT
        Gate::define('gestionarDocumento', [DocumentoFctPolicy::class, 'gestionarDocumento']);
        Gate::define('eliminarDocumento',  [DocumentoFctPolicy::class, 'eliminarDocumento']);

        // SeguimientoDiarioPolicy
        Gate::define('crearSeguimiento',    [SeguimientoDiarioPolicy::class, 'crear']);
        Gate::define('editarSeguimiento',   [SeguimientoDiarioPolicy::class, 'editar']);
        Gate::define('confirmarSeguimiento',[SeguimientoDiarioPolicy::class, 'confirmar']);

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Rate limiter login: 5 intentos/minuto por username+IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower($request->input('username', '')) . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withInput(request()->only('username', 'remember'))
                        ->withErrors([
                            'username' => 'Demasiados intentos fallidos. Espera 1 minuto antes de volver a intentarlo.',
                        ]);
                });
        });

        // Inyectar notificaciones no leídas en el layout
        View::composer('layouts.app', function ($view) {
            $notificacionesNav = auth()->check()
                ? Notificacion::where('user_id', auth()->id())->latest()->limit(10)->get()
                : collect();
            $view->with('notificacionesNav', $notificacionesNav);
        });

        // Rate limiter búsqueda global: 60 peticiones/minuto por usuario
        RateLimiter::for('busqueda', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json(['error' => 'Demasiadas peticiones. Espera un momento.'], 429);
                });
        });
        // Rate limiter acceso tutor empresa (magic link, sin sesión): 20 peticiones/minuto por IP
        RateLimiter::for('tutor', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->ip())
                ->response(function () {
                    abort(429, 'Demasiadas peticiones. Inténtalo de nuevo en un minuto.');
                });
        });
        // Rate limiter login portal externo (alumno/tutor_empresa): 5 intentos/minuto por email+IP
        RateLimiter::for('portal-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower($request->input('email', '')) . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withInput(request()->only('email', 'remember'))
                        ->withErrors([
                            'email' => 'Demasiados intentos fallidos. Espera 1 minuto antes de volver a intentarlo.',
                        ]);
                });
        });
    }
}
