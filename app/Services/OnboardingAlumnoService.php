<?php

namespace App\Services;

use App\Mail\BienvenidaAlumnoMail;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingAlumnoService
{
    /**
     * Crea la cuenta de usuario para un alumno y envía el email de bienvenida.
     * Se llama al crear la primera AsignacionFct del alumno.
     */
    public function crearCuentaAlumno(Alumno $alumno): User
    {
        if ($alumno->user_id && $alumno->user) {
            return $alumno->user;
        }

                // Si ya existe un usuario con ese email, vincularlo y no crear otro
        $existente = User::where('email', $alumno->email)->first();
        if ($existente) {
            $alumno->update(['user_id' => $existente->id]);
            return $existente;
        }

        $passwordTemporal = Str::password(10, symbols: false);

        $user = User::create([
            'username'                 => $this->generarUsername($alumno),
            'nombre'                   => $alumno->nombre . ' ' . $alumno->apellidos,
            'email'                    => $alumno->email,
            'password'                 => bcrypt($passwordTemporal),
            'rol'                      => 'alumno',
            'activo'                   => true,
            'password_change_required' => true,
        ]);

        $alumno->update(['user_id' => $user->id]);

        Mail::to($alumno->email)->send(new BienvenidaAlumnoMail($alumno, $passwordTemporal));

        return $user;
    }

    /**
     * Reactiva la cuenta de un alumno que fue desactivado en reset de curso.
     */
    public function reactivarCuenta(Alumno $alumno): void
    {
        if ($alumno->user) {
            $alumno->user->update(['activo' => true]);
        }
    }

    /**
     * Desactiva la cuenta al resetear el curso.
     */
    public function desactivarCuenta(Alumno $alumno): void
    {
        if ($alumno->user) {
            $alumno->user->update(['activo' => false]);
        }
    }

    private function generarUsername(Alumno $alumno): string
    {
        $base = Str::slug($alumno->nombre . '.' . $alumno->apellidos, '.');
        $base = substr($base, 0, 50);
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }
}
