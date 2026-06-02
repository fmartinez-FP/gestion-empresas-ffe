<?php

namespace App\Mail;

use App\Models\Alumno;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaAlumnoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Alumno $alumno,
        public readonly string $passwordTemporal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acceso al Portal de Prácticas FFE — ' . config('centro.nombre_corto', 'FFE'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-alumno',
        );
    }
}
