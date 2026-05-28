<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada en EmpresaController
    }

    public function rules(): array
    {
        $empresaId = $this->route('empresa')->id;

        return [
            'nombre'           => 'required|string|max:200',
            'cif'              => 'required|string|max:15|unique:empresas,cif,' . $empresaId,
            'num_convenio'     => 'nullable|string|max:50',
            'fecha_firma'      => 'nullable|date',
            'notas'            => 'nullable|string',
            'ciclos'           => 'nullable|array',
            'creador_id'       => 'nullable|exists:users,id',
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
