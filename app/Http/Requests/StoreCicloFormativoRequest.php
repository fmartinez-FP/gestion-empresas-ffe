<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCicloFormativoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada por middleware can:admin
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:10|unique:ciclos_formativos,codigo',
            'nombre' => 'required|string|max:150',
            'nivel'  => 'required|in:basica,media,superior',
            'activo' => 'boolean',
        ];
    }
}
