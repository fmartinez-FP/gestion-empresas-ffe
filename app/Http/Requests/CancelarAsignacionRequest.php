<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('cancelarAsignacion', $this->route('asignacion'));
    }

    public function rules(): array
    {
        return [
            'motivo_baja' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_baja.required' => 'Debes indicar el motivo de la cancelación.',
            'motivo_baja.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ];
    }
}
