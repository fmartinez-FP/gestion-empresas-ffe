<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'registrado_por_id',
        'tipo',
        'resultado',
        'persona_contacto',
        'notas',
        'fecha_contacto',
        'fecha_seguimiento',
        'archivo_adjunto',
        'archivo_nombre',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
        'fecha_seguimiento' => 'datetime',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    // Accessors
    public function getTipoEtiquetaAttribute(): string
    {
        return match($this->tipo) {
            'llamada' => 'Llamada telefónica',
            'email' => 'Email',
            'visita' => 'Visita presencial',
            'reunion_online' => 'Reunión online',
            'otro' => 'Otro',
            default => $this->tipo,
        };
    }

    public function getTipoIconoAttribute(): string
    {
        return match($this->tipo) {
            'llamada' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
            'email' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'visita' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
            'reunion_online' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            default => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        };
    }

    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'llamada' => 'blue',
            'email' => 'purple',
            'visita' => 'green',
            'reunion_online' => 'cyan',
            default => 'slate',
        };
    }

    public function getResultadoEtiquetaAttribute(): string
    {
        return match($this->resultado) {
            'exitoso' => 'Exitoso',
            'sin_respuesta' => 'Sin respuesta',
            'pendiente' => 'Pendiente de respuesta',
            'cita_programada' => 'Cita programada',
            default => $this->resultado,
        };
    }

    public function getResultadoColorAttribute(): string
    {
        return match($this->resultado) {
            'exitoso' => 'green',
            'sin_respuesta' => 'red',
            'pendiente' => 'yellow',
            'cita_programada' => 'blue',
            default => 'slate',
        };
    }

    // Scopes
    public function scopeDeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeConSeguimientoPendiente($query)
    {
        return $query->whereNotNull('fecha_seguimiento')
                     ->where('fecha_seguimiento', '>=', now());
    }
}
