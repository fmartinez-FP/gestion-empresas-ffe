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
        return $this->esResponsableSuperior($user);
    }

    public function editarAlumno(User $user, ?Alumno $alumno = null): bool
    {
        return $this->esGestorAlumnos($user);
    }

    /**
     * Nombre y apellidos: admin, responsable_ffe y responsable_ciclo pueden
     * editarlos. Profesor (tutor) no -- solo email/telefono (sesion 2026-08-10).
     */
    public function editarIdentidadAlumno(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe', 'responsable_ciclo']);
    }

    /**
     * grupo_id (y curso_academico, ligado al mismo cambio de matricula):
     * solo admin y responsable_ffe. Ni profesor ni responsable_ciclo pueden
     * reasignar el grupo/ciclo de un alumno (sesion 2026-08-10).
     */
    public function editarGrupoAlumno(User $user): bool
    {
        return $this->esResponsableSuperior($user);
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
