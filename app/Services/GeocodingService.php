<?php

namespace App\Services;

use App\Models\Direccion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public static function geocodificar(Direccion $direccion): bool
    {
        $query = implode(', ', array_filter([
            ($direccion->tipo_via ? "{$direccion->tipo_via} " : '') . $direccion->nombre_via,
            $direccion->numero,
            $direccion->codigo_postal,
            $direccion->municipio,
            'España',
        ]));

        try {
            $response = Http::withHeaders([
                'User-Agent' => config('centro.geocoding_ua'),
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                'q'            => $query,
                'format'       => 'json',
                'limit'        => 1,
                'countrycodes' => 'es',
            ]);

            $data = $response->json();

            if (!empty($data[0])) {
                $direccion->update([
                    'latitud'  => $data[0]['lat'],
                    'longitud' => $data[0]['lon'],
                ]);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Geocodificación fallida para direccion #{$direccion->id}: {$e->getMessage()}");
        }

        return false;
    }
}
