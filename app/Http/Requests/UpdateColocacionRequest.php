<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateColocacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada en ColocacionController
    }

    public function rules(): array
    {
        return [
            'ciclo_id'      => 'required|exists:ciclos_formativos,id',
            'numero_curso'  => 'required|integer|in:1,2',
            'num_alumnos'   => 'required|integer|min:1',
            'num_horas'     => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'ciclo_id.required'     => 'El ciclo formativo es obligatorio.',
            'numero_curso.required' => 'Indica si es 1º o 2º.',
            'num_alumnos.required'  => 'El número de alumnos es obligatorio.',
            'num_alumnos.min'       => 'Debe haber al menos 1 alumno.',
            'num_horas.required'    => 'El número de horas es obligatorio.',
            'num_horas.min'         => 'Debe haber al menos 1 hora.',
        ];
    }
}
