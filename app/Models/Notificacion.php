<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['user_id', 'tipo', 'titulo', 'url', 'modelo', 'modelo_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
