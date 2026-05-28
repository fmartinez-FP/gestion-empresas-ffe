<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorización gestionada en ContactoController
    }

    public function rules(): array
    {
        return [
            'tipo'             => 'required|in:llamada,email,visita,reunion_online,otro',
            'resultado'        => 'required|in:exitoso,sin_respuesta,pendiente,cita_programada',
            'persona_contacto' => 'nullable|string|max:255',
            'notas'            => 'nullable|string|max:5000',
            'fecha_contacto'   => 'required|date',
            'fecha_seguimiento' => 'nullable|date', // Sin after_or_equal: puede ser pasada al editar
            'archivo'          => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'eliminar_archivo' => 'nullable|boolean',
        ];
    }
}
