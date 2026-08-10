<?php

namespace App\Http\Requests;

use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('editarAlumno', $this->route('alumno'));
    }

    /**
     * Reglas condicionadas por rol (sesion 2026-08-10): profesor y
     * responsable_ciclo solo editan contacto (email/telefono); nombre y
     * apellidos requieren editarIdentidadAlumno; grupo_id/curso_academico
     * requieren editarGrupoAlumno. Los campos que el rol no puede tocar no
     * se validan porque la vista no los envia (inputs disabled), evitando
     * que un profesor reciba un error de "campo obligatorio" por un campo
     * que ni siquiera deberia ver como editable.
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'email'    => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];

        if ($user->can('editarIdentidadAlumno', Alumno::class)) {
            $rules['nombre']    = ['required', 'string', 'max:100'];
            $rules['apellidos'] = ['required', 'string', 'max:150'];
        }

        if ($user->can('editarGrupoAlumno', Alumno::class)) {
            // grupo_id ya cubre ciclo/curso de forma indirecta (ver Alumno::getCicloAttribute()/
            // getNumeroCursoAttribute()): no hay campo ciclo_id independiente en el formulario,
            // por lo que no hace falta -ni es posible- validar coherencia cruzada aqui.
            $rules['grupo_id']        = ['required', 'integer', 'exists:grupos,id'];
            $rules['curso_academico'] = ['required', 'string', 'regex:/^\d{4}-\d{4}$/'];
        }

        return $rules;
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
