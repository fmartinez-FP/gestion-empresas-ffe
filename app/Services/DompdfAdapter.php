<?php

namespace App\Services;

use App\Contracts\PdfGeneratorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class DompdfAdapter implements PdfGeneratorInterface
{
    public function generar(
        string $vista,
        array  $datos,
        string $rutaDisco,
        string $disco = 'private'
    ): string {
        $html = View::make($vista, $datos)->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $contenido = $dompdf->output();
        Storage::disk($disco)->put($rutaDisco, $contenido);

        return $rutaDisco;
    }
}
