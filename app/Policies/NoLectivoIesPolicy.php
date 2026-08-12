<?php

namespace App\Policies;

use App\Models\NoLectivoIes;
use App\Models\User;

class NoLectivoIesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdmin() || $user->esResponsableFFE();
    }

    public function create(User $user): bool
    {
        return $user->esAdmin() || $user->esResponsableFFE();
    }

    public function update(User $user, NoLectivoIes $noLectivoIes): bool
    {
        return $user->esAdmin() || $user->esResponsableFFE();
    }

    public function delete(User $user, NoLectivoIes $noLectivoIes): bool
    {
        return $user->esAdmin() || $user->esResponsableFFE();
    }
}
