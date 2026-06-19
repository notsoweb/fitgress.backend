<?php namespace App\Console\Commands;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Enviar correo de prueba
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class MailTestCommand extends Command
{
    /**
     * Nombre y firma del comando
     *
     * @var string
     */
    protected $signature = 'mail:test {email : Correo electrónico destino}';

    /**
     * Descripción del comando
     *
     * @var string
     */
    protected $description = 'Envía un correo de prueba para verificar la configuración de correo';

    /**
     * Ejecutar el comando
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("El correo [{$email}] no es válido.");

            return self::FAILURE;
        }

        $mailer = config('mail.default');

        $this->line("Mailer: {$mailer}");
        $this->line('From: '.config('mail.from.address').' ('.config('mail.from.name').')');

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER está en "log". El correo se escribirá en el log, no se enviará por SMTP.');
        }

        try {
            Mail::to($email)->send(new TestMail);
        } catch (Throwable $exception) {
            $this->error('No se pudo enviar el correo de prueba: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Correo de prueba enviado a {$email}.");

        if ($mailer === 'log') {
            $this->line('Revisa storage/logs/laravel.log para ver el contenido del correo.');
        }

        return self::SUCCESS;
    }
}
