<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentoFct extends Model
{
    use HasFactory;

    protected $table = 'documentos_fct';

    protected $fillable = [
        'asignacion_id',
        'tipo',
        'nombre_archivo',
        'ruta_disco',
        'disco',
        'generado_at',
        'subido_at',
        'purgar_after',
    ];

    protected $casts = [
        'generado_at'  => 'datetime',
        'subido_at'    => 'datetime',
        'purgar_after' => 'date',
    ];

    // =========================================================================
    // CONSTANTES
    // =========================================================================

    const TIPO_PLAN_FORMATIVO    = 'plan_formativo';
    const TIPO_FICHA_SEGUIMIENTO = 'ficha_seguimiento';
    const TIPO_INFORME_FINAL     = 'informe_final';
    const TIPO_FIRMADO           = 'firmado';

    const TIPOS_GENERABLES = [
        self::TIPO_PLAN_FORMATIVO,
        self::TIPO_FICHA_SEGUIMIENTO,
        self::TIPO_INFORME_FINAL,
    ];

    const ETIQUETAS_TIPO = [
        self::TIPO_PLAN_FORMATIVO    => 'Plan de Formación (Anexo 6)',
        self::TIPO_FICHA_SEGUIMIENTO => 'Ficha de Seguimiento (Anexo 8)',
        self::TIPO_INFORME_FINAL     => 'Informe de Valoración Final (Anexo 9)',
        self::TIPO_FIRMADO           => 'Documento Firmado',
    ];

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function etiqueta(): string
    {
        return self::ETIQUETAS_TIPO[$this->tipo] ?? $this->tipo;
    }

    public function esFirmado(): bool
    {
        return $this->tipo === self::TIPO_FIRMADO;
    }

    public function esGenerado(): bool
    {
        return in_array($this->tipo, self::TIPOS_GENERABLES);
    }

    /**
     * Genera un nombre de archivo determinista con timestamp.
     * Formato: {tipo}_{apellidos}_{nombre}_{empresa}_{timestamp}.pdf
     */
    public static function generarNombreArchivo(string $tipo, AsignacionFct $asignacion): string
    {
        $alumno  = $asignacion->alumno;
        $empresa = $asignacion->empresa;

        $partes = [
            $tipo,
            Str::slug($alumno->apellidos ?? 'alumno'),
            Str::slug($alumno->nombre    ?? ''),
            Str::slug($empresa->nombre   ?? 'empresa'),
            now()->format('YmdHis'),
        ];

        return implode('_', array_filter($partes)) . '.pdf';
    }

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }
}
