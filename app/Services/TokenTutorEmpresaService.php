<?php

namespace App\Services;

use App\Models\AsignacionFct;
use App\Models\TokenTutorEmpresa;
use Illuminate\Support\Str;

class TokenTutorEmpresaService
{
    public function generar(AsignacionFct $asignacion): array
    {
        // Invalidar tokens anteriores de esta asignacion
        $asignacion->tokenstutor()
            ->where("expires_at", ">", now())
            ->update(["expires_at" => now()]);

        do {
            $tokenPlano = Str::random(40);
            $tokenHash  = hash("sha256", $tokenPlano);
        } while (TokenTutorEmpresa::where("token", $tokenHash)->exists());

        $registro = TokenTutorEmpresa::create([
            "asignacion_id" => $asignacion->id,
            "token"         => $tokenHash,
            "expires_at"    => now()->addDays(30),
            "usado_at"      => null,
            "ip_uso"        => null,
        ]);

        return [
            "registro"   => $registro,
            "tokenPlano" => $tokenPlano,
        ];
    }

    public function validar(string $token): ?TokenTutorEmpresa
    {
        $tokenHash = hash("sha256", $token);
        $registro  = TokenTutorEmpresa::where("token", $tokenHash)->first();

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
