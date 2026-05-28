<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Colocacion;
use App\Models\Configuracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotificarAsignaciones extends Command
{
    protected $signature = 'asignaciones:notificar';
    protected $description = 'Resumen semanal de asignaciones en empresas que tutorizas';

    public function handle(): int
    {
        // Solo ejecutar entre 1 de septiembre y 30 de junio
        $hoy = Carbon::now();
        $mes = $hoy->month;
        
        if ($mes == 7 || $mes == 8) {
            $this->info('Notificaciones suspendidas en julio y agosto.');
            return 0;
        }

        $cursoActual = Configuracion::cursoActivo();
        $hace7Dias = Carbon::now()->subDays(7);

        // Obtener profesores que tienen empresas
        $profesores = User::whereNotNull('email')
            ->whereHas('empresasCreadas')
            ->get();

        $notificados = 0;

        foreach ($profesores as $profesor) {
            // Buscar asignaciones de la última semana en empresas del profesor
            // registradas por OTROS usuarios (no por él mismo)
            $asignaciones = Colocacion::where('created_at', '>=', $hace7Dias)
                ->whereHas('empresa', function ($q) use ($profesor) {
                    $q->where('creador_id', $profesor->id);
                })
                ->where('registrado_por_id', '!=', $profesor->id)
                ->with(['empresa', 'ciclo', 'registradoPor'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($asignaciones->isEmpty()) {
                continue;
            }

            try {
                Mail::send('emails.asignaciones-semana', [
                    'usuario' => $profesor,
                    'asignaciones' => $asignaciones,
                    'cursoActual' => $cursoActual,
                ], function ($message) use ($profesor) {
                    $message->to($profesor->email, $profesor->nombre)
                            ->subject('📋 Nuevas asignaciones en tus empresas - ' . config('centro.nombre_corto'));
                });
                
                $notificados++;
                $this->info("Notificado: {$profesor->nombre} ({$asignaciones->count()} asignaciones)");
            } catch (\Exception $e) {
                $this->error("Error notificando a {$profesor->nombre}: {$e->getMessage()}");
            }
        }

        $this->info("Total notificaciones enviadas: {$notificados}");
        return 0;
    }
}
