<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Services\CuadernoCalendarioService;
use App\Services\HorarioAsignacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeguimientoDiarioController extends Controller
{
    public function __construct(
        private CuadernoCalendarioService $calendarioService,
        private HorarioAsignacionService $horarioService,
    ) {}

    public function index(Request $request)
    {
        $user = auth('web_externo')->user();

        $asignacion = AsignacionFct::where('estado', 'activa')
            ->whereHas('alumno', fn ($q) => $q->where('user_id', $user->id))
            ->with(['alumno', 'empresa'])
            ->first();

        if ($asignacion === null) {
            return view('portal.cuaderno.sin-asignacion');
        }

        $mesActual = $this->calendarioService->resolverMesOverview($asignacion, $request->query('mes'));

        $seguimientos = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->whereBetween('fecha', [
                $mesActual->copy()->startOfMonth()->toDateString(),
                $mesActual->copy()->endOfMonth()->toDateString(),
            ])
            ->orderByDesc('fecha')
            ->get();

        $semanas = $seguimientos
            ->groupBy(fn ($s) => Carbon::parse($s->fecha)->startOfWeek(Carbon::MONDAY)->toDateString())
            ->sortKeysDesc()
            ->map(fn ($grupo, $lunes) => [
                'lunes'        => Carbon::parse($lunes),
                'seguimientos' => $grupo,
            ])
            ->values();

        $horasRealizadas = null;
        $horasPrevistas  = null;
        $horasPendientes = null;

        if ($asignacion->fecha_inicio !== null && $asignacion->fecha_fin !== null) {
            $horasRealizadas = $this->horarioService->horasRealizadas($asignacion);
            $horasPrevistas  = $this->horarioService->horasPrevistas($asignacion);
            $horasPendientes = max(0, round($horasPrevistas - $horasRealizadas, 2));
        }

        return view('portal.cuaderno.index', compact(
            'asignacion', 'semanas', 'mesActual', 'horasRealizadas', 'horasPrevistas', 'horasPendientes'
        ));
    }

    public function create()
    {
        $user = auth('web_externo')->user();

        $asignacion = AsignacionFct::where('estado', 'activa')
            ->whereHas('alumno', fn ($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        abort_unless(auth('web_externo')->user()->can('crearSeguimiento', $asignacion), 403);

        $hoy = Carbon::today()->toDateString();
        $yaRegistrado = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->where('fecha', $hoy)
            ->exists();

        if ($yaRegistrado) {
            return redirect()->route('portal.cuaderno.index')
                ->with('info', 'Ya tienes una entrada registrada para hoy.');
        }

        return view('portal.cuaderno.create', compact('asignacion'));
    }

    public function store(Request $request)
    {
        $user = auth('web_externo')->user();

        $asignacion = AsignacionFct::where('estado', 'activa')
            ->whereHas('alumno', fn ($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        abort_unless(auth('web_externo')->user()->can('crearSeguimiento', $asignacion), 403);

        $hoy = Carbon::today()->toDateString();
        $yaRegistrado = SeguimientoDiario::where('asignacion_id', $asignacion->id)
            ->where('fecha', $hoy)
            ->exists();

        abort_if($yaRegistrado, 422, 'Ya existe una entrada para hoy.');

        $validated = $request->validate([
            'descripcion_tareas' => ['required', 'string', 'max:2000'],
            'hora_entrada'       => ['required', 'date_format:H:i'],
            'hora_salida'        => ['required', 'date_format:H:i', 'after:hora_entrada'],
            'evidencia'          => ['nullable', 'image', 'max:5120'],
        ]);

        $evidenciaPath = null;
        if ($request->hasFile('evidencia')) {
            $evidenciaPath = $request->file('evidencia')->store(
                'seguimientos/' . $asignacion->id,
                'private'
            );
        }

        SeguimientoDiario::create([
            'asignacion_id'      => $asignacion->id,
            'fecha'              => $hoy,
            'descripcion_tareas' => $validated['descripcion_tareas'],
            'hora_entrada'       => $validated['hora_entrada'],
            'hora_salida'        => $validated['hora_salida'],
            'evidencia_path'     => $evidenciaPath,
            'confirmado_tutor'   => false,
        ]);

        return redirect()->route('portal.cuaderno.index')
            ->with('success', 'Entrada registrada correctamente.');
    }

    public function edit(SeguimientoDiario $seguimiento)
    {
        $user = auth('web_externo')->user();
        abort_unless($user->can('editarSeguimiento', $seguimiento), 403);

        return view('portal.cuaderno.edit', compact('seguimiento'));
    }

    public function update(Request $request, SeguimientoDiario $seguimiento)
    {
        $user = auth('web_externo')->user();
        abort_unless($user->can('editarSeguimiento', $seguimiento), 403);

        $validated = $request->validate([
            'descripcion_tareas' => ['required', 'string', 'max:2000'],
            'hora_entrada'       => ['required', 'date_format:H:i'],
            'hora_salida'        => ['required', 'date_format:H:i', 'after:hora_entrada'],
        ]);

        $seguimiento->update($validated);

        return redirect()->route('portal.cuaderno.index')
            ->with('success', 'Entrada actualizada correctamente.');
    }
}
