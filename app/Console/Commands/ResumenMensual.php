<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Colocacion;
use App\Models\Configuracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ResumenMensual extends Command
{
    protected $signature = 'resumen:mensual';
    protected $description = 'Resumen mensual de actividad FCT (febrero-mayo)';

    public function handle(): int
    {
        $hoy = Carbon::now();
        $mes = $hoy->month;
        
        // Solo ejecutar en febrero (2), marzo (3), abril (4) y mayo (5)
        if (!in_array($mes, [2, 3, 4, 5])) {
            $this->info('El resumen mensual solo se envía de febrero a mayo.');
            return 0;
        }

        $cursoActual = Configuracion::cursoActivo();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();
        $nombreMes = $this->getNombreMes($mes);

        // Recopilar estadísticas del mes
        $stats = $this->getEstadisticasMes($inicioMes, $finMes, $cursoActual);

        // Enviar solo a responsables de ciclo, responsable FFE y admins
        $usuarios = User::whereNotNull('email')
            ->whereIn('rol', ['admin', 'responsable_ffe', 'responsable_ciclo'])
            ->get();

        $notificados = 0;

        foreach ($usuarios as $usuario) {
            // Personalizar estadísticas por rol
            $statsUsuario = $this->getEstadisticasParaUsuario($usuario, $inicioMes, $finMes, $cursoActual);

            try {
                Mail::send('emails.resumen-mensual', [
                    'usuario' => $usuario,
                    'stats' => $statsUsuario,
                    'nombreMes' => $nombreMes,
                    'cursoActual' => $cursoActual,
                ], function ($message) use ($usuario, $nombreMes) {
                    $message->to($usuario->email, $usuario->nombre)
                            ->subject("📊 Resumen FCT {$nombreMes} - " . config('centro.nombre_corto'));
                });
                
                $notificados++;
                $this->info("Notificado: {$usuario->nombre}");
            } catch (\Exception $e) {
                $this->error("Error notificando a {$usuario->nombre}: {$e->getMessage()}");
            }
        }

        $this->info("Total notificaciones enviadas: {$notificados}");
        return 0;
    }

    private function getEstadisticasMes(Carbon $inicio, Carbon $fin, string $curso): array
    {
        $asignaciones = Colocacion::whereBetween('created_at', [$inicio, $fin])
            ->where('curso_academico', $curso);

        $nuevasEmpresas = Empresa::whereBetween('created_at', [$inicio, $fin]);

        $conveniosRenovados = Empresa::whereBetween('updated_at', [$inicio, $fin])
            ->whereColumn('updated_at', '!=', 'created_at')
            ->whereNotNull('fecha_firma');

        return [
            'num_asignaciones' => (clone $asignaciones)->count(),
            'alumnos_colocados' => (clone $asignaciones)->sum('num_alumnos'),
            'horas_totales' => (clone $asignaciones)->selectRaw('SUM(num_alumnos * num_horas) as total')->value('total') ?? 0,
            'nuevas_empresas' => (clone $nuevasEmpresas)->count(),
            'convenios_renovados' => (clone $conveniosRenovados)->count(),
            'convenios_caducados' => Empresa::estadoConvenio('caducado')->count(),
            'convenios_por_caducar' => Empresa::estadoConvenio('por_caducar')->count(),
        ];
    }

    private function getEstadisticasParaUsuario(User $usuario, Carbon $inicio, Carbon $fin, string $curso): array
    {
        if ($usuario->esAdmin() || $usuario->esResponsableFFE()) {
            return $this->getEstadisticasMes($inicio, $fin, $curso);
        }

        // Responsable de ciclo: filtrar por sus ciclos
        $ciclosIds = $usuario->ciclos()->pluck('ciclos_formativos.id')->toArray();

        $asignaciones = Colocacion::whereBetween('created_at', [$inicio, $fin])
            ->where('curso_academico', $curso)
            ->whereIn('ciclo_id', $ciclosIds);

        $nuevasEmpresas = Empresa::whereBetween('created_at', [$inicio, $fin])
            ->whereHas('ciclos', fn($q) => $q->whereIn('ciclos_formativos.id', $ciclosIds));

        $empresasCiclos = Empresa::whereHas('ciclos', fn($q) => $q->whereIn('ciclos_formativos.id', $ciclosIds));

        return [
            'num_asignaciones' => (clone $asignaciones)->count(),
            'alumnos_colocados' => (clone $asignaciones)->sum('num_alumnos'),
            'horas_totales' => (clone $asignaciones)->selectRaw('SUM(num_alumnos * num_horas) as total')->value('total') ?? 0,
            'nuevas_empresas' => (clone $nuevasEmpresas)->count(),
            'convenios_renovados' => 0, // Difícil de calcular por ciclo
            'convenios_caducados' => (clone $empresasCiclos)->estadoConvenio('caducado')->count(),
            'convenios_por_caducar' => (clone $empresasCiclos)->estadoConvenio('por_caducar')->count(),
        ];
    }

    private function getNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $meses[$mes] ?? '';
    }
}
