<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use App\Models\Empresa;

class NotificacionService
{
    /**
     * Crea una notificación. Para tipo convenio_caducando aplica deduplicación:
     * no crea una nueva si ya existe una sin leer para ese modelo_id.
     */
    public static function crear(int $userId, string $tipo, string $titulo, string $url, ?string $modelo = null, ?int $modeloId = null): void
    {
        if ($tipo === 'convenio_caducando' && $modeloId) {
            $existe = Notificacion::where('user_id', $userId)
                ->where('tipo', 'convenio_caducando')
                ->where('modelo_id', $modeloId)
                ->exists();
            if ($existe) return;
        }

        Notificacion::create([
            'user_id'   => $userId,
            'tipo'      => $tipo,
            'titulo'    => $titulo,
            'url'       => $url,
            'modelo'    => $modelo,
            'modelo_id' => $modeloId,
        ]);
    }

    /**
     * Notifica a todos los responsables_ciclo cuyos ciclos intersectan
     * con los ciclos de la empresa. Excluye al usuario autenticado actual.
     */
    public static function notificarResponsablesCiclo(Empresa $empresa, string $tipo, string $titulo, string $url): void
    {
        $col = 'ciclos_formativos' . '.id';
        $ciclosIds = $empresa->ciclos()->pluck($col)->toArray();

        if (empty($ciclosIds)) return;

        $excludeId = auth()->id() ?? 0;

        User::where('rol', 'responsable_ciclo')
            ->where('id', '!=', $excludeId)
            ->whereHas('ciclos', fn($q) => $q->whereIn($col, $ciclosIds))
            ->each(fn(User $u) => self::crear($u->id, $tipo, $titulo, $url, 'Empresa', $empresa->id));
    }

    /**
     * Notifica a todos los responsables_ffe. Excluye al usuario autenticado actual.
     */
    public static function notificarResponsablesFFE(Empresa $empresa, string $tipo, string $titulo, string $url): void
    {
        $excludeId = auth()->id() ?? 0;

        User::where('rol', 'responsable_ffe')
            ->where('id', '!=', $excludeId)
            ->each(fn(User $u) => self::crear($u->id, $tipo, $titulo, $url, 'Empresa', $empresa->id));
    }
}
