<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el usuario administrador fijo de IES Pacifico. La autenticacion real
     * pasa siempre por LDAP (guard 'web', driver 'ldap' en config/auth.php);
     * el password aqui es un placeholder aleatorio que nunca se usa, igual que
     * el que User::booted() asigna a cualquier usuario creado via LDAP.
     */
    public function run(): void
    {
        // guid/domain deben coincidir con lo que LdapRecord calcula en el primer
        // login real (guid = uid de LDAP = 'admin', domain = conexion LDAP por
        // defecto = 'default', ver config/ldap.php). Sin esto, LdapRecord busca
        // por guid, no encuentra la fila sembrada, e intenta un INSERT propio
        // que colisiona con el unique de username/email (visto en produccion
        // real de esta sesion: SQLSTATE 1062 en el primer login tras el seed).
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nombre'   => 'Administrador',
                'email'    => 'fct.ies.pacifico.madrid@educa.madrid.org',
                'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                'rol'      => 'admin',
                'ciclo_id' => null,
                'activo'   => true,
                'guid'     => 'admin',
                'domain'   => 'default',
            ]
        );

        $this->command->info('✓ Usuario administrador creado:');
        $this->command->info('  Username: admin');
        $this->command->info('  Email: fct.ies.pacifico.madrid@educa.madrid.org');
        $this->command->info('  Autenticacion via LDAP unicamente.');
    }
}
