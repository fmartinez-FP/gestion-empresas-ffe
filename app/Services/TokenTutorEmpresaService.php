<?php

namespace App\Services;

use App\Models\AsignacionFct;
use App\Models\TokenTutorEmpresa;
use Illuminate\Support\Str;

class TokenTutorEmpresaService
{
    public function generar(AsignacionFct $asignacion): TokenTutorEmpresa
    {
        // Invalidar tokens anteriores de esta asignacion
        $asignacion->tokenstutor()
            ->where("expires_at", ">", now())
            ->update(["expires_at" => now()]);

        do {
            $token = hash("sha256", Str::random(40));
        } while (TokenTutorEmpresa::where("token", $token)->exists());

        return TokenTutorEmpresa::create([
            "asignacion_id" => $asignacion->id,
            "token"         => $token,
            "expires_at"    => now()->addDays(30),
            "usado_at"      => null,
            "ip_uso"        => null,
        ]);
    }

    public function validar(string $token): ?TokenTutorEmpresa
    {
        $registro = TokenTutorEmpresa::where("token", $token)->first();

        if ($registro === null) {
            return null;
        }

        if (! $registro->estaVigente()) {
            return null;
        }

        return $registro;
    }

    public function marcarUsado(TokenTutorEmpresa $tokenRegistro, string $ip): void
    {
        if ($tokenRegistro->usado_at !== null) {
            return;
        }

        $tokenRegistro->update([
            "usado_at" => now(),
            "ip_uso"   => $ip,
        ]);
    }
}
