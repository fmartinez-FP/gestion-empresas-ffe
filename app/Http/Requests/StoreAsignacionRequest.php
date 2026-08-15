<?php

namespace App\Http\Requests;

use App\Services\HorarioAsignacionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('crearAsignacion', $this->route('alumno'));
    }

    /**
     * Un profesor siempre es su propio tutor IES al crear una asignacion: se ignora
     * cualquier valor recibido del formulario y se fuerza server-side, para que no
     * pueda manipularse (bloqueado tambien visualmente en la vista).
     */
    protected function prepareForValidation(): void
    {
        if (auth()->user()->rol === 'profesor') {
            $this->merge(['tutor_ies_id' => auth()->id()]);
        }
    }

    public function rules(): array
    {
        return [
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
    }

    public function messages(): array
    {
        return [
            'empresa_id.required'      => 'Debes seleccionar una empresa.',
            'tutor_ies_id.required'    => 'Debes asignar un tutor del IES.',
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
                return; // ya cubierto por la regla 'horarios' => required
            }

            foreach (app(HorarioAsignacionService::class)->validarHorarios($horarios) as $error) {
                $validator->errors()->add('horarios', $error);
            }
        });
    }
}
