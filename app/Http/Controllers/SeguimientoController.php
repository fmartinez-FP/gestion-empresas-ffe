<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Contacto::with(['empresa', 'registradoPor'])
            ->whereNotNull('fecha_seguimiento');

        // Admin y responsable_ffe ven todos; el resto solo los suyos
        if (!$user->esAdmin() && !$user->esResponsableFFE()) {
            $query->where('registrado_por_id', $user->id);
        }

        $periodo = $request->get('periodo', 'pendientes');

        match($periodo) {
            'semana'   => $query->whereBetween('fecha_seguimiento', [now()->startOfDay(), now()->endOfWeek()]),
            'mes'      => $query->whereBetween('fecha_seguimiento', [now()->startOfDay(), now()->endOfMonth()]),
            'vencidos' => $query->where('fecha_seguimiento', '<', now()->startOfDay()),
            default    => $query->where('fecha_seguimiento', '>=', now()->startOfDay()),
        };

        $seguimientos = $query->orderBy('fecha_seguimiento')->paginate(20)->withQueryString();

        return view('seguimientos.index', compact('seguimientos', 'periodo'));
    }
}
