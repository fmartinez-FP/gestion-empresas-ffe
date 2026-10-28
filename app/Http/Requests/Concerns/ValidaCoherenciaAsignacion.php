<?php

namespace App\Http\Requests\Concerns;

use App\Models\AsignacionFct;
use App\Models\Direccion;
use App\Models\PersonaContacto;
use Illuminate\Validation\Validator;

/**
 * Validaciones cruzadas de una asignación FFE que las reglas por campo no
 * cubren: la sede y el tutor de empresa deben pertenecer a la empresa elegida
 * y el alumno no puede tener dos asignaciones activas solapadas en fechas.
 */
trait ValidaCoherenciaAsignacion
{
    protected function validarCoherenciaAsignacion(
        Validator $validator,
        int $alumnoId,
        string $estado = 'activa',
        ?int $ignorarAsignacionId = null,
    ): void {
        // Si ya hay errores por campo (ids inexistentes, fechas inválidas) no se cruza nada.
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $empresaId = (int) $this->input('empresa_id');

        if ($this->filled('sede_id')
            && ! Direccion::where('id', $this->input('sede_id'))->where('empresa_id', $empresaId)->exists()) {
            $validator->errors()->add('sede_id', 'La sede seleccionada no pertenece a la empresa elegida.');
        }

        if ($this->filled('tutor_empresa_id')
            && ! PersonaContacto::where('id', $this->input('tutor_empresa_id'))->where('empresa_id', $empresaId)->exists()) {
            $validator->errors()->add('tutor_empresa_id', 'El tutor de empresa seleccionado no pertenece a la empresa elegida.');
        }

        if ($estado === 'activa') {
            $solapada = AsignacionFct::where('alumno_id', $alumnoId)
                ->where('estado', 'activa')
                ->when($ignorarAsignacionId, fn ($q) => $q->where('id', '!=', $ignorarAsignacionId))
                ->whereDate('fecha_inicio', '<=', $this->input('fecha_fin'))
                ->whereDate('fecha_fin', '>=', $this->input('fecha_inicio'))
                ->exists();

            if ($solapada) {
                $validator->errors()->add('fecha_inicio', 'El alumno ya tiene otra asignación activa que se solapa con esas fechas.');
            }
        }
    }
}
