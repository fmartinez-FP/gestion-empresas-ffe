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
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        Carbon::setLocale('es');

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
    }
}
