<?php

namespace App\Policies;

use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\User;

class AsignacionPolicy
{
    private function esPersonalIes(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor']);
    }

    /**
     * Cualquier personal del IES puede crear asignaciones, pero un profesor
     * solo para alumnos de sus grupos tutorizados y del curso academico
     * activo -- mismo criterio que AlumnoPolicy::verAlumno(). Confirmado con
     * Fernando (sesion 2026-08-14): antes no se validaba el alumno en
     * absoluto, permitiendo a cualquier profesor crear asignaciones para
     * cualquier alumno del sistema.
     */
    public function crearAsignacion(User $user, Alumno $alumno): bool
    {
        if (! ($user->esAdmin() || $user->esResponsableFFE() || $user->esResponsableCiclo() || $user->esProfesor())) {
            return false;
        }

        return app(AlumnoPolicy::class)->verAlumno($user, $alumno);
    }

    /** Admin y responsable_ffe pueden cancelar; otros no */
    public function cancelarAsignacion(User $user, AsignacionFct $asignacion): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe'])
            && $asignacion->estado === 'activa';
    }

    /** Profesor solo ve las suyas; resto ve todas */
    public function verAsignacion(User $user, AsignacionFct $asignacion): bool
    {
        if ($user->rol === 'profesor') {
            return $asignacion->tutor_ies_id === $user->id;
        }
        return $this->esPersonalIes($user);
    }

    /** Admin y responsable_ffe pueden editar cualquiera; responsable_ciclo y profesor solo las suyas */
    public function editarAsignacion(User $user, AsignacionFct $asignacion): bool
    {
        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            return true;
        }
        if (in_array($user->rol, ['responsable_ciclo', 'profesor'])) {
            return $asignacion->tutor_ies_id === $user->id;
        }
        return false;
    }

    /** Mismo patron que confirmarSeguimiento: tutor_ies de la asignacion, o admin/responsable_ffe */
    public function ajustarHorasSemana(User $user, AsignacionFct $asignacion): bool
    {
        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            return true;
        }
        return $asignacion->tutor_ies_id === $user->id;
    }

    /** Mismo patron que confirmarSeguimiento/ajustarHorasSemana */
    public function marcarDiaNoTrabajado(User $user, AsignacionFct $asignacion): bool
    {
        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            return true;
        }
        return $asignacion->tutor_ies_id === $user->id;
    }
}
