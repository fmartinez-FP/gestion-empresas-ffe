<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonaContacto extends Model
{
    use HasFactory;

    protected $table = 'personas_contacto';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'cargo',
        'telefono',
        'email',
        'notas',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
