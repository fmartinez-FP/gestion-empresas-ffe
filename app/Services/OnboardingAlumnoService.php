<?php

namespace App\Services;

use App\Mail\BienvenidaAlumnoMail;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingAlumnoService
{
    /**
     * Aviso para mostrar al usuario si la última operación no pudo completarse
     * del todo (alumno sin email, email de otro usuario, fallo de SMTP). Nunca
     * se lanza excepción por estos casos: la asignación ya está guardada y la
     * cuenta se puede crear/reenviar después desde la ficha del alumno.
     */
    public ?string $aviso = null;

    /**
     * Crea la cuenta de usuario para un alumno y envía el email de bienvenida.
     * Se llama al crear la primera AsignacionFct del alumno.
     *
     * Devuelve null (y deja el motivo en $aviso) si no se puede crear o vincular
     * una cuenta de forma segura.
     */
    public function crearCuentaAlumno(Alumno $alumno): ?User
    {
        $this->aviso = null;

        if ($alumno->user_id && $alumno->user) {
            return $alumno->user;
        }

        if (blank($alumno->email)) {
            $this->aviso = 'El alumno no tiene email: no se ha creado su cuenta del portal. '
                . 'Añade el email en su ficha y usa «Resetear contraseña» para crearla.';
            return null;
        }

        // Si ya existe un usuario con ese email solo se vincula cuando es la cuenta
        // de portal de un alumno que no pertenece a otro registro; nunca una cuenta
        // del personal del centro (se le podría resetear la contraseña o desactivar).
        $existente = User::where('email', $alumno->email)->first();
        if ($existente) {
            $enUso = Alumno::withTrashed()
                ->where('user_id', $existente->id)
                ->where('id', '!=', $alumno->id)
                ->exists();

            if ($existente->rol !== 'alumno' || $enUso) {
                $this->aviso = "El email {$alumno->email} ya pertenece a otro usuario del sistema: "
                    . 'no se ha creado la cuenta del portal. Indica un email distinto en la ficha del alumno.';
                return null;
            }

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

        $this->enviarBienvenida($alumno, $passwordTemporal);

        return $user;
    }

    /**
     * Mantiene users.email alineado con alumnos.email (el login del portal usa
     * users.email). Devuelve false, sin tocar nada, si el email ya lo usa otro
     * usuario: users.email es único y fallaría con un 500.
     */
    public function sincronizarEmailCuenta(Alumno $alumno): bool
    {
        $user = $alumno->user;
        if (! $user || blank($alumno->email) || $user->email === $alumno->email) {
            return true;
        }

        if (User::where('email', $alumno->email)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->update(['email' => $alumno->email]);
        return true;
    }

    private function enviarBienvenida(Alumno $alumno, string $passwordTemporal): bool
    {
        try {
            Mail::to($alumno->email)->send(new BienvenidaAlumnoMail($alumno, $passwordTemporal));
            return true;
        } catch (\Throwable $e) {
            report($e);
            Log::warning("No se pudo enviar el email de bienvenida al alumno #{$alumno->id}: {$e->getMessage()}");
            $this->aviso = 'La cuenta del portal se ha creado, pero no se pudo enviar el email de bienvenida. '
                . 'Usa «Resetear contraseña» en la ficha del alumno para reenviarlo.';
            return false;
        }
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

    /**
     * Resetea la contraseña de un alumno con cuenta de portal ya creada:
     * genera una nueva temporal, fuerza cambio en el próximo login y
     * reenvía BienvenidaAlumnoMail con la nueva contraseña.
     *
     * @throws \RuntimeException si el alumno no tiene cuenta de portal.
     */
    public function resetearPassword(Alumno $alumno): void
    {
        $this->aviso = null;

        if (! $alumno->user) {
            throw new \RuntimeException('El alumno no tiene cuenta de portal creada.');
        }

        if (blank($alumno->email)) {
            throw new \RuntimeException('El alumno no tiene email: no se puede enviar la nueva contraseña.');
        }

        if (! $this->sincronizarEmailCuenta($alumno)) {
            throw new \RuntimeException("El email {$alumno->email} ya pertenece a otro usuario del sistema.");
        }

        $passwordTemporal = Str::password(10, symbols: false);

        $alumno->user->update([
            'password'                 => bcrypt($passwordTemporal),
            'password_change_required' => true,
        ]);

        // Aquí el envío es la acción pedida: si falla se propaga (no hay nada más que hacer).
        Mail::to($alumno->email)->send(new BienvenidaAlumnoMail($alumno, $passwordTemporal));
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
