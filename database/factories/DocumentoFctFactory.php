<?php

namespace Database\Factories;

use App\Models\AsignacionFct;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFctFactory extends Factory
{
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['plan_formativo', 'ficha_seguimiento', 'informe_final', 'firmado']);

        return [
            'asignacion_id'  => AsignacionFct::factory(),
            'tipo'           => $tipo,
            'nombre_archivo' => $tipo . '_' . $this->faker->numerify('####') . '.pdf',
            'ruta_disco'     => 'fct/1/' . $tipo . '.pdf',
            'disco'          => 'private',
            'generado_at'    => $tipo !== 'firmado' ? now() : null,
            'subido_at'      => $tipo === 'firmado'  ? now() : null,
            'purgar_after'   => $tipo === 'firmado'  ? now()->addYears(5) : null,
        ];
    }
}
