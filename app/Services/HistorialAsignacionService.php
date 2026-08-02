<?php

namespace App\Services;

use App\Models\AsignacionFct;
use App\Models\Colocacion;
use Illuminate\Support\Facades\DB;

class HistorialAsignacionService
{
    /**
     * Tramos de horas de 2º curso por nivel del ciclo.
     * 1º siempre son 150h, sin distinción de nivel.
     */
    private const TRAMOS_SEGUNDO = [
        'basica'   => [250, 400],
        'media'    => [350, 500],
        'superior' => [350, 500],
    ];

    /**
     * Registra una asignación finalizada en el histórico de colocaciones.
     * Idempotente: si la asignación ya está vinculada a una colocación
     * (tabla puente), no hace nada. No actúa si la asignación no está
     * finalizada (las canceladas nunca generan historial).
     */
    public function registrar(AsignacionFct $asignacion): void
    {
        if ($asignacion->estado !== 'finalizada') {
            return;
        }

        if (DB::table('colocacion_asignacion_fct')->where('asignacion_id', $asignacion->id)->exists()) {
            return;
        }

        $asignacion->loadMissing('ciclo');
        $horasTramo = $this->calcularHorasTramo($asignacion);

        DB::transaction(function () use ($asignacion, $horasTramo) {
            $colocacion = Colocacion::firstOrNew([
                'empresa_id'      => $asignacion->empresa_id,
                'ciclo_id'        => $asignacion->ciclo_id,
                'curso_academico' => $asignacion->curso_academico,
                'numero_curso'    => $asignacion->numero_curso,
                'num_horas'       => $horasTramo,
                'origen'          => 'automatica',
            ]);

            if (!$colocacion->exists) {
                $colocacion->registrado_por_id = $asignacion->tutor_ies_id;
                $colocacion->num_alumnos = 0;
            }

            $colocacion->num_alumnos += 1;
            $colocacion->save();

            DB::table('colocacion_asignacion_fct')->insert([
                'colocacion_id' => $colocacion->id,
                'asignacion_id' => $asignacion->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });
    }

    /**
     * Procesa una colección de asignaciones (uso: ffe:reset-curso).
     */
    public function registrarLote(iterable $asignaciones): void
    {
        foreach ($asignaciones as $asignacion) {
            $this->registrar($asignacion);
        }
    }

    private function calcularHorasTramo(AsignacionFct $asignacion): int
    {
        if ($asignacion->numero_curso === 1) {
            return 150;
        }

        $nivel = $asignacion->ciclo->nivel;
        [$opcionBaja, $opcionAlta] = self::TRAMOS_SEGUNDO[$nivel] ?? [350, 500];

        $horas = $asignacion->num_horas ?? 0;

        return abs($horas - $opcionBaja) <= abs($horas - $opcionAlta) ? $opcionBaja : $opcionAlta;
    }
}
