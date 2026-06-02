<?php

namespace App\Policies;

use App\Models\DocumentoFct;
use App\Models\User;

class DocumentoFctPolicy
{
    /**
     * Generar y descargar: cualquier rol del IES.
     */
    public function gestionarDocumento(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor']);
    }

    /**
     * Eliminar: solo admin y responsable_ffe.
     */
    public function eliminarDocumento(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe']);
    }
}
