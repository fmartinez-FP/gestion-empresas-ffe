<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (Auth::guest()) {
            if ($request->expectsJson()) {
                abort(401, 'No autenticado.');
            }
            
            return redirect()->guest(route('login'));
        }

        // Verificar que el usuario está activo
        if (!Auth::user()->activo) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta ha sido desactivada. Contacta con el administrador.',
            ]);
        }

        return $next($request);
    }
}
