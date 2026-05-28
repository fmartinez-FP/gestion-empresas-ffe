<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|max:150',
            'cargo'     => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'notas'     => 'nullable|string',
            'principal' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la persona de contacto es obligatorio.',
            'email.email'     => 'El email no tiene un formato válido.',
        ];
    }
}
