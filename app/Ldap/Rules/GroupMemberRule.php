<?php

namespace App\Ldap\Rules;

use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class GroupMemberRule implements Rule
{
    public function passes(LdapModel $user, ?EloquentModel $model = null): bool
    {
        $group  = env('LDAP_APP_GROUP', '');
        $baseDn = config('ldap.connections.default.base_dn');

        // 1. shadowExpire = '0' significa cuenta desactivada
        $shadowExpire = $user->getFirstAttribute('shadowExpire');
        if ($shadowExpire !== null && $shadowExpire === '0') {
            return false;
        }

        // 2. Comprobar pertenencia al grupo via memberOf del usuario.
        //    Evitamos leer el atributo 'member' del grupo porque cn=readonly
        //    no tiene ACL para ello. El overlay memberof de OpenLDAP mantiene
        //    memberOf en el usuario sincronizado automáticamente.
        $groupDn  = "cn={$group},ou=groups,{$baseDn}";
        $memberOf = $user->getAttribute('memberof') ?? [];

        // Comparación case-insensitive (los DN en LDAP no son case-sensitive)
        return in_array(
            strtolower($groupDn),
            array_map('strtolower', $memberOf),
            true
        );
    }
}
