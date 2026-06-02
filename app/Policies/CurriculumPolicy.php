<?php

namespace App\Policies;

use App\Models\User;

class CurriculumPolicy
{
    public function verCurriculum(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe', 'responsable_ciclo', 'profesor']);
    }

    public function gestionarCurriculum(User $user): bool
    {
        return $user->rol === 'admin';
    }

    public function gestionarElegibles(User $user): bool
    {
        return in_array($user->rol, ['admin', 'responsable_ffe']);
    }
}
