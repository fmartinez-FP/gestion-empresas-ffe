<?php

namespace App\Http\Requests\Concerns;

use App\Models\CicloFormativo;
use Illuminate\Validation\Validator;

/**
 * El formulario envía ciclos[<id_ciclo>][seleccionado|acepta_*]: el id va en la
 * clave, no en un campo "id", así que la regla ciclos.*.id nunca validaba nada
 * y un id inexistente llegaba al sync() y fallaba con 500 por la FK.
 */
trait ValidaCiclosSeleccionados
{
    public function after(): array
    {
        return [function (Validator $validator) {
            $ids = array_keys((array) $this->input('ciclos', []));
            if ($ids === []) {
                return;
            }

            $existentes = CicloFormativo::whereIn('id', $ids)->count();
            if ($existentes !== count($ids)) {
                $validator->errors()->add('ciclos', 'Alguno de los ciclos seleccionados no existe.');
            }
        }];
    }
}
