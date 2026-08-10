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
        // grupo_id ya cubre ciclo/curso de forma indirecta (ver Alumno::getCicloAttribute()/
        // getNumeroCursoAttribute()): no hay campo ciclo_id independiente en el formulario,
        // por lo que no hace falta -ni es posible- validar coherencia cruzada aqui.
        return [
            'nombre'          => ['required', 'string', 'max:100'],
            'apellidos'       => ['required', 'string', 'max:150'],
            'email'           => ['nullable', 'email', 'max:255'],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'grupo_id'        => ['required', 'integer', 'exists:grupos,id'],
            'curso_academico' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'          => 'El nombre es obligatorio.',
            'apellidos.required'       => 'Los apellidos son obligatorios.',
            'grupo_id.required'        => 'Debes seleccionar un grupo.',
            'curso_academico.regex'    => 'El curso académico debe tener el formato AAAA-AAAA (ej. 2025-2026).',
        ];
    }
}
