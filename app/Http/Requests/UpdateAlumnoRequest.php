<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('editarAlumno', $this->route('alumno'));
    }

    public function rules(): array
    {
        return [
            'nombre'          => ['required', 'string', 'max:100'],
            'apellidos'       => ['required', 'string', 'max:150'],
            'email'           => ['nullable', 'email', 'max:255'],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'ciclo_id'        => ['required', 'integer', 'exists:ciclos_formativos,id'],
            'curso_academico' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'numero_curso'    => ['required', 'integer', 'in:1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'          => 'El nombre es obligatorio.',
            'apellidos.required'       => 'Los apellidos son obligatorios.',
            'ciclo_id.required'        => 'Debes seleccionar un ciclo formativo.',
            'curso_academico.regex'    => 'El curso académico debe tener el formato AAAA-AAAA (ej. 2025-2026).',
            'numero_curso.required'    => 'Debes indicar el número de curso.',
        ];
    }
}
