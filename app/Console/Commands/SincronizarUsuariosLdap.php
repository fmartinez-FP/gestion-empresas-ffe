<?php

namespace App\Console\Commands;

use App\Services\Ldap\SincronizacionLdapService;
use Illuminate\Console\Command;

class SincronizarUsuariosLdap extends Command
{
    protected $signature = 'ffe:sincronizar-ldap {--simular : Muestra los cambios sin aplicarlos}';

    protected $description = 'Alinea los usuarios con LDAP: bajas, usuarios borrados y rol de administrador';

    public function handle(SincronizacionLdapService $sincronizacion): int
    {
        try {
            $resultado = $sincronizacion->sincronizar($this->option('simular'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $titulos = [
            'desactivados' => 'Desactivados', 'reactivados' => 'Reactivados', 'eliminados' => 'Eliminados',
            'conservados'  => 'No eliminados', 'ascendidos' => 'Pasan a administrador',
            'degradados'   => 'Dejan de ser administrador', 'avisos' => 'Avisos',
        ];

        $hayCambios = false;
        foreach ($titulos as $clave => $titulo) {
            if ($resultado[$clave] !== []) {
                $hayCambios = true;
                $this->line("{$titulo}: " . implode(', ', $resultado[$clave]));
            }
        }

        $this->info(
            ($this->option('simular') ? '[simulación] ' : '')
            . ($hayCambios ? 'Sincronización completada.' : 'Todo estaba al día.')
        );

        return self::SUCCESS;
    }
}
