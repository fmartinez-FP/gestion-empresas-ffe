<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('editarAsignacion', $this->route('asignacion'));
    }

    public function rules(): array
    {
        $user = auth()->user();
        $rules = [
            'empresa_id'       => ['required', 'integer', 'exists:empresas,id'],
            'sede_id'          => ['nullable', 'integer', 'exists:direcciones,id'],
            'tutor_empresa_id' => ['nullable', 'integer', 'exists:personas_contacto,id'],
            'tutor_ies_id'     => ['required', 'integer', 'exists:users,id'],
            'fecha_inicio'     => ['nullable', 'date'],
            'fecha_fin'        => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'num_horas'        => ['nullable', 'integer', 'min:1', 'max:9999'],
            'horario'          => ['nullable', 'string', 'max:500'],
            'ra_ids'           => ['nullable', 'array'],
            'ra_ids.*'         => ['integer', 'exists:resultados_aprendizaje,id'],
            'ce_ids'           => ['nullable', 'array'],
            'ce_ids.*'         => ['integer', 'exists:criterios_evaluacion,id'],
        ];

        if (in_array($user->rol, ['admin', 'responsable_ffe'])) {
            $rules['estado'] = ['sometimes', 'in:activa,finalizada,cancelada'];
        }

        return $rules;
    }
}
