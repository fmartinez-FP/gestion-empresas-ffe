<?php

namespace App\Http\Controllers;

use App\Models\CicloFormativo;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('verCurriculum'), 403);

        $ciclos = CicloFormativo::withCount(['modulos', 'modulos as ra_count' => function ($q) {
            $q->join('resultados_aprendizaje', 'modulos_profesionales.id', '=', 'resultados_aprendizaje.modulo_id');
        }])->orderBy('nombre')->get();

        return view('curriculum.index', compact('ciclos'));
    }
}
