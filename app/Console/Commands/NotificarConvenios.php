<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificacionService;
use Carbon\Carbon;

class NotificarConvenios extends Command
{
    protected $signature = 'convenios:notificar';
    protected $description = 'Notifica por email los convenios próximos a caducar (personalizado por rol)';

    public function handle(): int
    {
        // Solo ejecutar entre 1 de septiembre y 30 de junio
        $hoy = Carbon::now();
        $mes = $hoy->month;
        
        if ($mes == 7 || $mes == 8) {
            $this->info('Notificaciones suspendidas en julio y agosto.');
            return 0;
        }

        $usuarios = User::whereNotNull('email')->get();
        $notificados = 0;

        foreach ($usuarios as $usuario) {
            $empresas = $this->getEmpresasParaUsuario($usuario);
            
            if ($empresas->isEmpty()) {
                continue;
            }

            try {
                Mail::send('emails.convenios-caducar', [
                    'usuario' => $usuario,
                    'empresas' => $empresas,
                ], function ($message) use ($usuario) {
                    $message->to($usuario->email, $usuario->nombre)
                            ->subject('⚠️ Convenios próximos a caducar - ' . config('centro.nombre_corto'));
                });
                
                $notificados++;
                $this->info("Notificado: {$usuario->nombre} ({$empresas->count()} empresas)");
            } catch (\Exception $e) {
                $this->error("Error notificando a {$usuario->nombre}: {$e->getMessage()}");
            }

            // Notificaciones in-app (independiente del email, con deduplicación)
            foreach ($empresas as $empresa) {
                NotificacionService::crear(
                    $usuario->id,
                    'convenio_caducando',
                    'Convenio próximo a caducar: ' . $empresa->nombre,
                    route('empresas.show', $empresa),
                    'Empresa',
                    $empresa->id
                );
            }
        }

        $this->info("Total notificaciones enviadas: {$notificados}");
        return 0;
    }

    private function getEmpresasParaUsuario(User $usuario)
    {
        $query = Empresa::estadoConvenio('por_caducar')
            ->with(['ciclos', 'creador'])
            ->orderBy('fecha_firma');

        if ($usuario->esAdmin() || $usuario->esResponsableFFE()) {
            // Todas las empresas
            return $query->get();
        } elseif ($usuario->esResponsableCiclo()) {
            // Solo empresas de sus ciclos
            $ciclosIds = $usuario->ciclos()->pluck('ciclos_formativos.id')->toArray();
            return $query->whereHas('ciclos', function ($q) use ($ciclosIds) {
                $q->whereIn('ciclos_formativos.id', $ciclosIds);
            })->get();
        } else {
            // Profesor: solo sus empresas
            return $query->where('creador_id', $usuario->id)->get();
        }
    }
}
