<?php

namespace App\Mail;

/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de prueba
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Obtener el sobre del mensaje
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Correo de prueba - '.config('app.name'),
        );
    }

    /**
     * Obtener la definición del contenido del mensaje
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test',
            with: [
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
                'mailer' => config('mail.default'),
                'sentAt' => now()->toDateTimeString(),
            ],
        );
    }
}
