<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionFctFactory extends Factory
{
    protected $model = AsignacionFct::class;

    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('2025-09-01', '2026-01-01');
        $fechaFin    = fake()->dateTimeBetween('2026-02-01', '2026-06-30');

        return [
            'alumno_id'              => Alumno::factory(),
            'empresa_id'             => Empresa::factory(),
            'sede_id'                => null,
            'tutor_empresa_id'       => null,
            'tutor_ies_id'           => User::factory(),
            'ciclo_id'               => CicloFormativo::factory(),
            'curso_academico'        => '2025-2026',
            'numero_curso'           => fake()->randomElement([1, 2]),
            'fecha_inicio'           => $fechaInicio,
            'fecha_fin'              => $fechaFin,
            'num_horas'              => fake()->randomElement([240, 370, 400]),
            'horario'                => 'Lunes a viernes de 9:00 a 14:00',
            'calendario'             => null,
            'estado'                 => 'activa',
            'motivo_baja'            => null,
            'intervalo_email_tutor'  => 14,
            'ultimo_email_tutor_at'  => null,
        ];
    }

    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'activa',
        ]);
    }

    public function finalizada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'finalizada',
        ]);
    }

    public function cancelada(string $motivo = 'Baja voluntaria del alumno'): static
    {
        return $this->state(fn (array $attributes) => [
            'estado'      => 'cancelada',
            'motivo_baja' => $motivo,
        ]);
    }
}
