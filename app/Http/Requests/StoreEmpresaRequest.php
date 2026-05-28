<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada en EmpresaController
    }

    public function rules(): array
    {
        return [
            'nombre'           => 'required|string|max:200',
            'cif'              => 'required|string|max:15|unique:empresas,cif',
            'contacto_nombre'   => 'nullable|string|max:150',
            'contacto_cargo'    => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email'    => 'nullable|email|max:255',
            'num_convenio'     => 'nullable|string|max:50',
            'fecha_firma'      => 'nullable|date',
            'notas'            => 'nullable|string',
            'ciclos'           => 'nullable|array',
            'ciclos.*.id'      => 'exists:ciclos_formativos,id',
            'ciclos.*.acepta_primero' => 'boolean',
            'ciclos.*.acepta_segundo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'cif.required'    => 'El CIF es obligatorio.',
            'cif.unique'      => 'Ya existe una empresa con este CIF.',
        ];
    }
}
