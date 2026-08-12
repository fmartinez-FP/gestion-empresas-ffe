<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoLectivoIes extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'no_lectivos_ies';

    protected $fillable = [
        'fecha',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
}
