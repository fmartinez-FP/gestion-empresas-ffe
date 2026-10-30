<?php

namespace App\Services\Ldap;

use App\Ldap\User as LdapUser;
use RuntimeException;

/**
 * Lectura del directorio LDAP: estado de cada cuenta y pertenencia a los
 * grupos de la aplicación. Es el único punto que habla con el servidor, de
 * modo que el resto de la sincronización se puede probar sin LDAP.
 */
class DirectorioLdap
{
    /**
     * Todas las cuentas del directorio, indexadas por uid en minúsculas.
     *
     * @return array<string, array{activo: bool, en_ffe: bool, admin: bool}>
     */
    public function usuarios(): array
    {
        try {
            $cuentas = LdapUser::query()->paginate(500);
        } catch (\Throwable $e) {
            throw new RuntimeException('No se pudo leer el directorio LDAP: ' . $e->getMessage(), 0, $e);
        }

        $resultado = [];
        foreach ($cuentas as $cuenta) {
            $uid = $cuenta->getFirstAttribute('uid');
            if ($uid !== null && $uid !== '') {
                $resultado[mb_strtolower($uid)] = $this->estado($cuenta);
            }
        }

        return $resultado;
    }

    /**
     * Estado de una cuenta concreta, o null si ya no existe en el directorio.
     *
     * @return array{activo: bool, en_ffe: bool, admin: bool}|null
     */
    public function usuario(string $uid): ?array
    {
        $cuenta = LdapUser::query()->where('uid', '=', $uid)->first();

        return $cuenta ? $this->estado($cuenta) : null;
    }

    private function estado(LdapUser $cuenta): array
    {
        $miembroDe = array_map('mb_strtolower', $cuenta->getAttribute('memberof') ?? []);

        return [
            // shadowExpire = 0 es una cuenta desactivada (misma regla que el login).
            'activo' => $cuenta->getFirstAttribute('shadowExpire') !== '0',
            'en_ffe' => in_array($this->dnGrupo(config('ldap.app_group')), $miembroDe, true),
            'admin'  => in_array($this->dnGrupo(config('ldap.admin_group')), $miembroDe, true),
        ];
    }

    private function dnGrupo(string $grupo): string
    {
        $baseDn = config('ldap.connections.default.base_dn');

        return mb_strtolower("cn={$grupo},ou=groups,{$baseDn}");
    }
}
