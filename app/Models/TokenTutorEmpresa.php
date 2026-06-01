<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTutorEmpresa extends Model
{
    use HasFactory;

    protected $table = 'tokens_tutor_empresa';

    protected $fillable = [
        'asignacion_id',
        'token',
        'expires_at',
        'usado_at',
        'ip_uso',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'usado_at'   => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionFct::class, 'asignacion_id');
    }

    public function estaVigente(): bool
    {
        return $this->usado_at === null && $this->expires_at->isFuture();
    }
}
