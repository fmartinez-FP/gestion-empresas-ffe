<?php

namespace App\Http\Requests;

use App\Services\HorarioAsignacionService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('editarAsignacion', $this->route('asignacion'));
    }

    /**
     * Un profesor siempre es su propio tutor IES: se ignora cualquier valor recibido
     * del formulario y se fuerza server-side (bloqueado tambien visualmente en la vista).
     * En la practica ya solo puede editar asignaciones donde ya es tutor_ies_id, pero
     * esto evita que pueda transferirsela a otro profesor via el formulario.
     */
    protected function prepareForValidation(): void
    {
        if (auth()->user()->rol === 'profesor') {
            $this->merge(['tutor_ies_id' => auth()->id()]);
        }
    }

    public function rules(): array
    {
        $user = auth()->user();

        $rules = [
            'empresa_id'       => ['required', 'integer', 'exists:empresas,id'],
            'sede_id'          => ['nullable', 'integer', 'exists:direcciones,id'],
            'tutor_empresa_id' => ['nullable', 'integer', 'exists:personas_contacto,id'],
            'tutor_ies_id'     => ['required', 'integer', 'exists:users,id'],
            'fecha_inicio'     => ['required', 'date'],
            'fecha_fin'        => ['required', 'date', 'after_or_equal:fecha_inicio'],

            'horarios'                   => ['required', 'array', 'min:1'],
            'horarios.*.dia'             => ['required', 'string', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'horarios.*.entrada_manana'  => ['required', 'date_format:H:i'],
            'horarios.*.salida_manana'   => ['required', 'date_format:H:i'],
            'horarios.*.entrada_tarde'   => ['nullable', 'date_format:H:i'],
            'horarios.*.salida_tarde'    => ['nullable', 'date_format:H:i'],

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

    public function messages(): array
    {
        return [
            'fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'       => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'horarios.required'        => 'Debes configurar al menos un día de horario.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $horarios = $this->input('horarios');

            if (!is_array($horarios) || empty($horarios)) {
                return;
            }

            foreach (app(HorarioAsignacionService::class)->validarHorarios($horarios) as $error) {
                $validator->errors()->add('horarios', $error);
            }
        });
    }
}
