<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarcarRangoNoLectivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\NoLectivoIes::class);
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after_or_equal:fecha_inicio', 'before_or_equal:fecha_inicio +3 months'],
            'motivo'       => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.before_or_equal' => 'El rango no puede superar los 3 meses de duración.',
        ];
    }
}
