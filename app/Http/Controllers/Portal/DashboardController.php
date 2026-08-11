<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\HorarioAsignacionService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private HorarioAsignacionService $horarioService) {}

    public function index()
    {
        $user = Auth::guard('web_externo')->user();
        $alumno = $user->alumno;

        $horasRealizadas = null;
        $horasPrevistas  = null;
        $horasPendientes = null;

        if ($alumno && $alumno->asignacionActiva
            && $alumno->asignacionActiva->fecha_inicio !== null
            && $alumno->asignacionActiva->fecha_fin !== null) {
            $asignacion       = $alumno->asignacionActiva;
            $horasRealizadas  = $this->horarioService->horasRealizadas($asignacion);
            $horasPrevistas   = $this->horarioService->horasPrevistas($asignacion);
            $horasPendientes  = max(0, round($horasPrevistas - $horasRealizadas, 2));
        }

        return view('portal.dashboard', compact(
            'user', 'alumno', 'horasRealizadas', 'horasPrevistas', 'horasPendientes'
        ));
    }
}
