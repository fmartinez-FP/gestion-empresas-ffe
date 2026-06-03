<?php

namespace App\Console\Commands;

use App\Mail\SeguimientosPendientesMail;
use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarTutoresCommand extends Command
{
    protected $signature   = 'ffe:notificar-tutores
                                {--dias= : Umbral de dias sin confirmar (sobreescribe intervalo_email_tutor)}';

    protected $description = 'Envía email al tutor IES de asignaciones con seguimientos sin confirmar superiores al umbral de días';

    public function handle(): int
    {
        $umbralGlobal = $this->option('dias') ? (int) $this->option('dias') : null;

        $asignaciones = AsignacionFct::where('estado', 'activa')
            ->with(['alumno', 'empresa', 'tutorIes'])
            ->get();

        $enviados = 0;

        foreach ($asignaciones as $asignacion) {
            $umbral = $umbralGlobal ?? $asignacion->intervalo_email_tutor ?? 14;

            $fechaLimite = Carbon::today()->subDays($umbral);

            $pendientes = SeguimientoDiario::where('asignacion_id', $asignacion->id)
                ->where('confirmado_tutor', false)
                ->where('fecha', '<=', $fechaLimite->toDateString())
                ->orderBy('fecha')
                ->get();

            if ($pendientes->isEmpty()) {
                continue;
            }

            $tutorIes = $asignacion->tutorIes;

            if ($tutorIes === null || empty($tutorIes->email)) {
                $this->warn("Asignación #{$asignacion->id}: tutor IES sin email, omitido.");
                continue;
            }

            Mail::to($tutorIes->email)->send(
                new SeguimientosPendientesMail($asignacion, $pendientes)
            );

            $asignacion->update(['ultimo_email_tutor_at' => now()]);

            $this->info("Notificado: {$tutorIes->email} — {$pendientes->count()} pendientes en asignación #{$asignacion->id}");
            $enviados++;
        }

        $this->info("Total notificaciones enviadas: {$enviados}");
        return Command::SUCCESS;
    }
}
