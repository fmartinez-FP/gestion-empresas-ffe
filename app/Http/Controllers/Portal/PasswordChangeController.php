<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function show()
    {
        return view('portal.password.change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::guard('web_externo')->user();
        $user->update([
            'password'                => Hash::make($request->password),
            'password_change_required' => false,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
