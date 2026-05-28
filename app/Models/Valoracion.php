<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones';

    protected $fillable = [
        'empresa_id',
        'curso_academico',
        'valorado_por_id',
        'trato_alumno',
        'calidad_formacion',
        'seguimiento_tutor',
        'comunicacion_ies',
        'posibilidad_contratacion',
        'observaciones',
    ];

    protected $casts = [
        'trato_alumno' => 'integer',
        'calidad_formacion' => 'integer',
        'seguimiento_tutor' => 'integer',
        'comunicacion_ies' => 'integer',
        'posibilidad_contratacion' => 'integer',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function valoradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valorado_por_id');
    }

    // Accessors
    public function getMediaAttribute(): float
    {
        $criterios = [
            $this->trato_alumno,
            $this->calidad_formacion,
            $this->seguimiento_tutor,
            $this->comunicacion_ies,
            $this->posibilidad_contratacion,
        ];
        
        $valorados = array_filter($criterios, fn($v) => $v > 0);
        
        if (count($valorados) === 0) {
            return 0;
        }
        
        return round(array_sum($valorados) / count($valorados), 1);
    }

    public static function getCriterios(): array
    {
        return [
            'trato_alumno' => [
                'nombre' => 'Trato al alumno',
                'descripcion' => 'Respeto, integración y ambiente laboral',
                'icono' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'calidad_formacion' => [
                'nombre' => 'Calidad de la formación',
                'descripcion' => 'Aprendizaje técnico y profesional',
                'icono' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            ],
            'seguimiento_tutor' => [
                'nombre' => 'Seguimiento del tutor',
                'descripcion' => 'Implicación del tutor de empresa',
                'icono' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            ],
            'comunicacion_ies' => [
                'nombre' => 'Comunicación IES-Empresa',
                'descripcion' => 'Fluidez en la comunicación con el centro',
                'icono' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            ],
            'posibilidad_contratacion' => [
                'nombre' => 'Posibilidad de contratación',
                'descripcion' => 'Opciones de empleo tras las prácticas',
                'icono' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
        ];
    }
}
