<?php

namespace App\Mail;

use App\Models\AsignacionFct;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SeguimientosPendientesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AsignacionFct $asignacion,
        public Collection $seguimientosPendientes
    ) {}

    public function envelope(): Envelope
    {
        $alumno = $this->asignacion->alumno;
        return new Envelope(
            subject: '[FFE] Seguimientos pendientes de validar — ' . $alumno->nombre . ' ' . $alumno->apellidos,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seguimientos-pendientes',
        );
    }
}
