<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    const TIPOS_VIA = [
        'Calle', 'Avenida', 'Paseo', 'Plaza', 'Carretera',
        'Camino', 'Ronda', 'Travesía', 'Urbanización',
        'Polígono Industrial', 'Otra',
    ];

    protected $table = 'direcciones';

    protected $fillable = [
        'empresa_id', 'tipo_via', 'nombre_via', 'numero',
        'codigo_postal', 'municipio', 'latitud', 'longitud', 'principal',
    ];

    protected $casts = [
        'latitud'   => 'float',
        'longitud'  => 'float',
        'principal' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getFormatoCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->tipo_via ? "{$this->tipo_via} {$this->nombre_via}" : $this->nombre_via,
            $this->numero,
            trim("{$this->codigo_postal} {$this->municipio}"),
        ]);
        return implode(', ', $partes);
    }

    public function isGeocodificada(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }
}
