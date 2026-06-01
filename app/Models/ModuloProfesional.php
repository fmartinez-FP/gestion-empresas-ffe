<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloProfesional extends Model
{
    use HasFactory;

    protected $table = 'modulos_profesionales';

    protected $fillable = [
        'ciclo_id',
        'codigo',
        'nombre',
        'horas_totales',
        'curso',
    ];

    protected $casts = [
        'horas_totales' => 'integer',
        'curso'         => 'integer',
    ];

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class, 'ciclo_id');
    }

    public function resultadosAprendizaje(): HasMany
    {
        return $this->hasMany(ResultadoAprendizaje::class, 'modulo_id');
    }
}
