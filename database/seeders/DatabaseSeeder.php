<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║   Inicializando Base de Datos - IES Pacífico             ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Orden importante: ciclos antes que usuarios (por la FK)
        $this->call([
            CicloFormativoSeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Base de datos inicializada correctamente.');
        $this->command->info('');
    }
}
