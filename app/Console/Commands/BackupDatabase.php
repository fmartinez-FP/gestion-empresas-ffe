<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7}';
    protected $description = 'Backup de la base de datos';

    public function handle(): int
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $file = "{$dir}/backup_{$db}_" . Carbon::now()->format('Y-m-d_His') . ".sql";

        exec(sprintf('mysqldump -u%s -p%s -h%s %s > %s 2>&1',
            escapeshellarg($user), escapeshellarg($pass), 
            escapeshellarg($host), escapeshellarg($db), escapeshellarg($file)
        ), $out, $code);

        if ($code !== 0) {
            $this->error('Error al crear backup');
            return 1;
        }

        exec("gzip {$file}");
        $this->info('Backup: ' . basename($file) . '.gz');

        // Limpiar antiguos
        $files = glob("{$dir}/backup_*.sql.gz");
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        foreach (array_slice($files, (int)$this->option('keep')) as $old) {
            unlink($old);
            $this->info('Eliminado: ' . basename($old));
        }

        return 0;
    }
}
