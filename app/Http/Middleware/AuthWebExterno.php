<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthWebExterno
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web_externo')->check()) {
            return redirect()->route('portal.login');
        }

        $user = Auth::guard('web_externo')->user();

        if (!in_array($user->rol, ['alumno', 'tutor_empresa'])) {
            Auth::guard('web_externo')->logout();
            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Acceso no autorizado.']);
        }

        if ($user->password_change_required) {
            if (!$request->routeIs('portal.password.change', 'portal.password.update')) {
                return redirect()->route('portal.password.change');
            }
        }

        return $next($request);
    }
}
