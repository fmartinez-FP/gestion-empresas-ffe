<?php

namespace Database\Factories;

use App\Models\AsignacionFct;
use App\Models\TokenTutorEmpresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TokenTutorEmpresaFactory extends Factory
{
    protected $model = TokenTutorEmpresa::class;

    public function definition(): array
    {
        return [
            "asignacion_id" => AsignacionFct::factory(),
            "token"         => hash("sha256", Str::random(40)),
            "expires_at"    => now()->addDays(30),
            "usado_at"      => null,
            "ip_uso"        => null,
        ];
    }

    public function expirado(): static
    {
        return $this->state(fn (array $attributes) => [
            "expires_at" => now()->subDay(),
        ]);
    }

    public function usado(): static
    {
        return $this->state(fn (array $attributes) => [
            "usado_at" => now()->subHours(2),
            "ip_uso"   => "192.168.1.100",
        ]);
    }
}
