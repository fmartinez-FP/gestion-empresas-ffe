<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreValoracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada en ValoracionController
    }

    public function rules(): array
    {
        return [
            'trato_alumno'            => 'required|integer|min:1|max:5',
            'calidad_formacion'       => 'required|integer|min:1|max:5',
            'seguimiento_tutor'       => 'required|integer|min:1|max:5',
            'comunicacion_ies'        => 'required|integer|min:1|max:5',
            'posibilidad_contratacion' => 'required|integer|min:1|max:5',
            'observaciones'           => 'nullable|string|max:2000',
        ];
    }
}
