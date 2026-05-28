<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'user_id',
        'user_nombre',
        'modelo',
        'modelo_id',
        'accion',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function registrarCreacion(Model $modelo, string $descripcion = null): self
    {
        return self::registrar($modelo, 'crear', $descripcion, null, $modelo->toArray());
    }

    public static function registrarActualizacion(Model $modelo, array $datosAnteriores, string $descripcion = null): self
    {
        return self::registrar($modelo, 'actualizar', $descripcion, $datosAnteriores, $modelo->toArray());
    }

    public static function registrarEliminacion(Model $modelo, string $descripcion = null): self
    {
        return self::registrar($modelo, 'eliminar', $descripcion, $modelo->toArray(), null);
    }

    public static function registrarAsignacion(Model $modelo, string $descripcion = null): self
    {
    	return self::registrar($modelo, 'asignacion', $descripcion, null, $modelo->toArray());
    }

    protected static function registrar(Model $modelo, string $accion, ?string $descripcion, ?array $datosAnteriores, ?array $datosNuevos): self
    {
        $user = auth()->user();
        $nombreModelo = class_basename($modelo);
        $identificador = $modelo->nombre ?? $modelo->id;
        
        return self::create([
            'user_id' => $user?->id,
            'user_nombre' => $user?->nombre ?? 'Sistema',
            'modelo' => $nombreModelo,
            'modelo_id' => $modelo->id,
            'accion' => $accion,
            'descripcion' => $descripcion ?? match($accion) {
                'crear' => "Se creó {$nombreModelo}: {$identificador}",
                'actualizar' => "Se actualizó {$nombreModelo}: {$identificador}",
                'eliminar' => "Se eliminó {$nombreModelo}: {$identificador}",
                default => "{$accion} en {$nombreModelo}",
            },
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip' => request()->ip(),
        ]);
    }

    public function getAccionEtiquetaAttribute(): string
    {
        return match($this->accion) {
            'crear' => 'Creación',
            'actualizar' => 'Actualización',
            'eliminar' => 'Eliminación',
	    'acceso' => 'Acceso',
	    'asignacion' => 'Asignación',
            default => ucfirst($this->accion),
        };
    }

    public function getAccionColorAttribute(): string
    {
        return match($this->accion) {
            'crear' => 'green',
            'actualizar' => 'blue',
            'eliminar' => 'red',
	    'acceso' => 'blue',
	    'asignacion' => 'slate',
            default => 'gray',
        };
    }
}
