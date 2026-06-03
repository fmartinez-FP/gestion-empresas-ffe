<?php

namespace App\Policies;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\User;
use Carbon\Carbon;

class SeguimientoDiarioPolicy
{
    /**
     * El alumno puede crear una entrada si:
     * - La asignacion esta activa
     * - La fecha es hoy o un dia pasado dentro del periodo
     * El guard web_externo pasa un User con rol alumno.
     */
    public function crear(User $user, AsignacionFct $asignacion): bool
    {
        if ($asignacion->estado !== 'activa') {
            return false;
        }

        $hoy = Carbon::today();

        if ($asignacion->fecha_inicio === null || $hoy->lt($asignacion->fecha_inicio)) {
            return false;
        }

        if ($asignacion->fecha_fin !== null && $hoy->gt($asignacion->fecha_fin)) {
            return false;
        }

        return $asignacion->alumno->user_id === $user->id;
    }

    /**
     * El alumno puede editar una entrada solo si:
     * - La entrada es de hoy
     * - No esta confirmada por el tutor IES
     * - Es el dueno de la asignacion
     */
    public function editar(User $user, SeguimientoDiario $seguimiento): bool
    {
        if ($seguimiento->confirmado_tutor) {
            return false;
        }

        if (! $seguimiento->fecha->isToday()) {
            return false;
        }

        return $seguimiento->asignacion->alumno->user_id === $user->id;
    }

    /**
     * El tutor IES de la asignacion, admin y responsable_ffe pueden confirmar.
     * Se llama con el guard web (personal IES).
     */
    public function confirmar(User $user, SeguimientoDiario $seguimiento): bool
    {
        if ($seguimiento->confirmado_tutor) {
            return false;
        }

        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            return true;
        }

        return $seguimiento->asignacion->tutor_ies_id === $user->id;
    }
}
