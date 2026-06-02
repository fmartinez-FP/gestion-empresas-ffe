<?php

namespace App\Contracts;

use App\Models\AsignacionFct;
use App\Models\DocumentoFct;

interface PdfGeneratorInterface
{
    /**
     * Genera un PDF a partir de una vista Blade y lo persiste en disco.
     *
     * @param  string        $vista      Nombre de la vista Blade (ej: 'pdf.plan_formativo')
     * @param  array         $datos      Variables que se pasarán a la vista
     * @param  string        $rutaDisco  Ruta relativa dentro del disco (ej: 'fct/1/plan.pdf')
     * @param  string        $disco      Nombre del disco de Storage (default: 'private')
     * @return string                    Ruta relativa donde se guardó el archivo
     */
    public function generar(
        string $vista,
        array  $datos,
        string $rutaDisco,
        string $disco = 'private'
    ): string;
}
