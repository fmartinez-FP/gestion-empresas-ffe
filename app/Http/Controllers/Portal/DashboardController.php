<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web_externo')->user();
        $alumno = $user->alumno;
        return view('portal.dashboard', compact('user', 'alumno'));
    }
}
