<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador por defecto
        $admin = User::updateOrCreate(
            ['email' => 'admin@iespacifico.es'],
            [
                'nombre' => 'Administrador',
                'password' => Hash::make('admin1234'), // ¡CAMBIAR EN PRODUCCIÓN!
                'rol' => 'admin',
                'ciclo_id' => null,
                'activo' => true,
            ]
        );

        $this->command->info('✓ Usuario administrador creado:');
        $this->command->info('  Email: admin@iespacifico.es');
        $this->command->info('  Contraseña: admin1234');
        $this->command->warn('  ⚠️  ¡IMPORTANTE! Cambie la contraseña tras el primer acceso.');
    }
}
