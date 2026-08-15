<?php

namespace App\Policies;

use App\Models\AsignacionFct;
use App\Models\DocumentoFct;
use App\Models\User;

class DocumentoFctPolicy
{
    /**
     * Generar, descargar y subir: admin/responsable_ffe sin restriccion, o el
     * tutor_ies especifico de esa asignacion (sea cual sea su rol). Un
     * responsable_ciclo que no sea el tutor_ies asignado NO tiene acceso.
     * Confirmado con Fernando (sesion 2026-08-14): antes cualquier profesor o
     * responsable_ciclo del IES podia generar/descargar/subir documentos de
     * CUALQUIER asignacion, sin relacion con el alumno.
     */
    public function gestionarDocumento(User $user, AsignacionFct $asignacion): bool
    {
        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            return true;
        }

        return $asignacion->tutor_ies_id === $user->id;
    }

    /**
     * Eliminar: solo admin y responsable_ffe.
     */
    public function eliminarDocumento(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe']);
    }
}
