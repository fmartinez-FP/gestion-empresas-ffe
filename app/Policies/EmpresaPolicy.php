<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function update(User $user, Empresa $empresa): bool
    {
        if ($user->esAdmin() || $user->esResponsableFFE()) {
            return true;
        }
        if ($user->esProfesor()) {
            return $empresa->creador_id === $user->id;
        }
        if ($user->esResponsableCiclo()) {
            if ($empresa->creador_id === $user->id) {
                return true;
            }
            $ciclosIds = $user->ciclos()->pluck('ciclos_formativos.id')->toArray();
            return !empty($ciclosIds) && $empresa->ciclos()
                ->whereIn('ciclos_formativos.id', $ciclosIds)
                ->exists();
        }
        return false;
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->esAdmin();
    }

    public function colocar(User $user, Empresa $empresa): bool
    {
        return $empresa->ciclos()->exists();
    }

    public function verPersonasContacto(User $user, Empresa $empresa): bool
    {
        return $this->update($user, $empresa);
    }

    public function verAuditoria(User $user, Empresa $empresa): bool
    {
        if ($user->esAdmin() || $user->esResponsableFFE()) {
            return true;
        }
        if ($user->esProfesor()) {
            return $empresa->creador_id === $user->id;
        }
        if ($user->esResponsableCiclo()) {
            $col = 'ciclos_formativos' . '.id';
            $ciclosIds = $user->ciclos()->pluck($col)->toArray();
            return !empty($ciclosIds) && $empresa->ciclos()
                ->whereIn($col, $ciclosIds)
                ->exists();
        }
        return false;
    }

}
