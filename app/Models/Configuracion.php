<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    /**
     * Obtener un valor de configuración
     */
    public static function obtener(string $clave, $default = null): ?string
    {
        return Cache::remember("config_{$clave}", 3600, function () use ($clave, $default) {
            $config = self::where('clave', $clave)->first();
            return $config ? $config->valor : $default;
        });
    }

    /**
     * Establecer un valor de configuración
     */
    public static function establecer(string $clave, string $valor, ?string $descripcion = null): void
    {
        self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'descripcion' => $descripcion]
        );
        Cache::forget("config_{$clave}");
    }

    /**
     * Obtener el curso activo actual
     */
    public static function cursoActivo(): string
    {
        return self::obtener('curso_activo', self::calcularCursoActual());
    }

    /**
     * Establecer el curso activo
     */
    public static function setCursoActivo(string $curso): void
    {
        self::establecer('curso_activo', $curso, 'Curso académico activo para registro de colocaciones');
    }

    /**
     * Calcular el curso académico actual basado en la fecha
     * (Septiembre a Agosto = mismo curso)
     */
    public static function calcularCursoActual(): string
    {
        $mes = (int) date('n');
        $anio = (int) date('Y');
        
        // Si estamos entre enero y agosto, el curso empezó el año anterior
        if ($mes < 9) {
            $anioInicio = $anio - 1;
        } else {
            $anioInicio = $anio;
        }
        
        return $anioInicio . '-' . ($anioInicio + 1);
    }

    /**
     * Generar lista de cursos disponibles (5 años atrás + 2 adelante)
     */
    public static function cursosDisponibles(): array
    {
        $cursoActual = self::calcularCursoActual();
        $anioActual = (int) explode('-', $cursoActual)[0];
        
        $cursos = [];
        for ($i = $anioActual - 5; $i <= $anioActual + 2; $i++) {
            $clave = $i . '-' . ($i + 1);
            $cursos[$clave] = 'Curso ' . $clave;
        }
        
        return $cursos;
    }

    /**
     * Obtener el curso siguiente al activo
     */
    public static function cursoSiguiente(): string
    {
        $activo = self::cursoActivo();
        $anioInicio = (int) explode('-', $activo)[0];
        return ($anioInicio + 1) . '-' . ($anioInicio + 2);
    }
}
