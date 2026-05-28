<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ConvenioPorCaducar extends Notification
{
    use Queueable;

    public function __construct(protected Collection $empresas) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->empresas->count() . ' convenios próximos a caducar - ' . config('centro.nombre_corto'))
            ->greeting('Hola ' . $notifiable->nombre)
            ->line('Convenios próximos a caducar:');

        foreach ($this->empresas as $e) {
            $mail->line("• {$e->nombre} (CIF: {$e->cif}) - Caduca en {$e->dias_hasta_vencimiento} días");
        }

        return $mail->action('Ver empresas', url('/empresas'))
            ->line('Recuerda renovar los convenios antes de su vencimiento.');
    }
}
