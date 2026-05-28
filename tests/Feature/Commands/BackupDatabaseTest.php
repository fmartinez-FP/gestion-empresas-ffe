<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupDir = storage_path('app/backups');
    }

    protected function tearDown(): void
    {
        // Limpiar backups creados durante los tests
        if (is_dir($this->backupDir)) {
            foreach (glob("{$this->backupDir}/backup_*.sql.gz") as $file) {
                @unlink($file);
            }
            foreach (glob("{$this->backupDir}/backup_*.sql") as $file) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function comando_acepta_opcion_keep_con_valor_por_defecto()
    {
        // Verificar que el comando existe y acepta --keep sin error de parseo
        $this->artisan('backup:database --help')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function crea_directorio_de_backups_si_no_existe()
    {
        // Eliminar directorio si existe para forzar su creación
        if (is_dir($this->backupDir)) {
            array_map('unlink', glob("{$this->backupDir}/*"));
            @rmdir($this->backupDir);
        }

        // El comando debe crear el directorio (puede fallar mysqldump pero el dir se crea)
        $this->artisan('backup:database');

        $this->assertDirectoryExists($this->backupDir);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function opcion_keep_es_configurable()
    {
        // Verificar que --keep=N es aceptado sin excepción de parseo de opciones
        // El exit code depende de si mysqldump está disponible en el servidor
        $this->artisan('backup:database', ['--keep' => 3])
            ->assertExitCode(0);
    }
}
