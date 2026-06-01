<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoFct extends Model
{
    use HasFactory;

    protected $table = 'documentos_fct';

    protected $fillable = [
        'asignacion_id',
        'tipo',
        'archivo_path',
        'generado_at',
        'firmado',
        'firmado_at',
        'archivo_firmado_path',
        'purgar_after',
    ];

    protected $casts = [
        'generado_at'  => 'datetime',
        'firmado'      => 'boolean',
        'firmado_at'   => 'datetime',
        'purgar_after' => 'date',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }
}
