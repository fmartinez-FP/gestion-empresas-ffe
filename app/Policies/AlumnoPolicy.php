<?php

namespace App\Policies;

use App\Models\Alumno;
use App\Models\User;

class AlumnoPolicy
{
    /**
     * Personal IES con permiso para crear/editar alumnos: admin, responsable_ffe,
     * responsable_ciclo, profesor. Ver tabla de roles, ARQUITECTURA v16 sección 4.
     */
    private function esGestorAlumnos(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor']);
    }

    /**
     * Acciones de gestión sensible restringidas a admin/responsable_ffe únicamente
     * (dar de baja, ver archivo, importar). Mismo nivel que "Eliminar alumnos" en
     * la tabla de roles v16 sección 4.
     */
    private function esResponsableSuperior(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe']);
    }

    public function crearAlumno(User $user): bool
    {
        return $this->esGestorAlumnos($user);
    }

    public function editarAlumno(User $user, ?Alumno $alumno = null): bool
    {
        return $this->esGestorAlumnos($user);
    }
    public function resetearPasswordAlumno(User $user, ?Alumno $alumno = null): bool
    {
        return $this->esGestorAlumnos($user);
    }

    public function eliminarAlumno(User $user): bool
    {
        return $this->esResponsableSuperior($user);
    }

    public function verArchivo(User $user): bool
    {
        return $this->esResponsableSuperior($user);
    }

    public function importarAlumnos(User $user): bool
    {
        return $this->esResponsableSuperior($user);
    }
}
