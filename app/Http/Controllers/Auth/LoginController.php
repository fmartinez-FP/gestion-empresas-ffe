<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // LdapRecord usa el KEY del array como atributo LDAP de búsqueda.
        // El atributo LDAP es 'uid', no 'username'.
        $credentials = [
            'uid'      => $request->input('username'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            try {
                \App\Models\Auditoria::create([
                    'user_id'          => $user->id,
                    'user_nombre'      => $user->nombre,
                    'modelo'           => 'User',
                    'modelo_id'        => $user->id,
                    'accion'           => 'acceso',
                    'descripcion'      => "Inicio de sesión: {$user->nombre}",
                    'datos_anteriores' => null,
                    'datos_nuevos'     => null,
                    'ip'               => $request->ip(),
                ]);
            } catch (\Exception $e) {}

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors([
                'username' => 'Las credenciales no coinciden o el usuario está desactivado.',
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
