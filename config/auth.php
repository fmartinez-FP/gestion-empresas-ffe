<?php

use App\Models\User;
use App\Ldap\User as LdapUser;

return [
    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'web_externo' => [
            'driver'   => 'session',
            'provider' => 'usuarios_externos',
        ],
    ],

    'providers' => [
        'users' => [
            'driver'          => 'ldap',
            'model'           => LdapUser::class,
            'locate_users_by' => 'uid',
            'bind_users_by'   => 'distinguishedname',
            'rules' => [\App\Ldap\Rules\GroupMemberRule::class],
            'database' => [
                'model'           => User::class,
                'guid_column'     => 'guid',
                'sync_passwords'  => false,
                'sync_attributes' => [
                    'username' => 'uid',
                    'nombre'   => 'cn',
                    'email'    => 'mail',
                ],
            ],
        ],
        'usuarios_externos' => [
            'driver' => 'eloquent',
            'model'  => User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
