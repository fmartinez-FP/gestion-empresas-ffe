<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionFct::class, 'tutor_empresa_id');
    }
}
