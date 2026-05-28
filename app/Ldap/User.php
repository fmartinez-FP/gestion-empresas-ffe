<?php

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\User as BaseUser;
use LdapRecord\Query\Model\Builder;

class User extends BaseUser
{
    protected string $guidKey = 'uid';

    public function newQuery(): Builder
    {
        return parent::newQuery()->select(['*', 'memberof']);
    }

    /**
     * Cuando se llama sin argumento (Synchronizer::createOrFindEloquentModel),
     * leemos uid directamente de los atributos.
     * Cuando se llama con argumento (convertAttributesToJson), lo devolvemos
     * tal cual — uid es un string plano, no necesita conversión binaria AD.
     */
    public function getConvertedGuid(?string $guid = null): ?string
    {
        return $guid ?? $this->getFirstAttribute($this->guidKey);
    }
}
