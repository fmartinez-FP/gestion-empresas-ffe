<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarcarRangoNoLectivoRequest;
use App\Models\NoLectivoIes;
use App\Services\NoLectivoIesService;
use Carbon\Carbon;

class NoLectivoIesController extends Controller
{
    public function __construct(private NoLectivoIesService $servicio) {}

    public function index()
    {
        abort_unless(auth()->user()->can('viewAny', NoLectivoIes::class), 403);

        $noLectivos = NoLectivoIes::orderBy('fecha')->get();

        return view('admin.calendario-ffe.index', compact('noLectivos'));
    }

    public function store(MarcarRangoNoLectivoRequest $request)
    {
        $validated = $request->validated();

        $inicio = Carbon::parse($validated['fecha_inicio']);
        $fin    = Carbon::parse($validated['fecha_fin']);

        $creados = $this->servicio->marcarRango($inicio, $fin, $validated['motivo'] ?? null);

        $warning = $inicio->lt(Carbon::today())
            ? ' Aviso: la fecha de inicio es anterior a hoy.'
            : '';

        return back()->with('success', "Se han marcado {$creados->count()} día(s) como no lectivos.{$warning}");
    }

    public function destroy(NoLectivoIes $noLectivoIe)
    {
        abort_unless(auth()->user()->can('delete', $noLectivoIe), 403);

        $this->servicio->eliminar($noLectivoIe);

        return back()->with('success', 'Día eliminado del calendario de centro.');
    }
}
