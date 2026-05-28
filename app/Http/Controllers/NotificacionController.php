<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\RedirectResponse;

class NotificacionController extends Controller
{
    public function leer(Notificacion $notificacion): RedirectResponse
    {
        abort_unless(auth()->id() === $notificacion->user_id, 403);

        $url = $notificacion->url;
        $notificacion->delete();

        return redirect($url ?: route('dashboard'));
    }
}
