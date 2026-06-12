<?php namespace App\Console\Commands;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Console\Command;

/**
 * Enviar notificación de prueba vía Reverb
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class NotifyTestCommand extends Command
{
    /**
     * Nombre y firma del comando
     *
     * @var string
     */
    protected $signature = 'notify:test {--email= : Correo electrónico del usuario destino}';

    /**
     * Descripción del comando
     *
     * @var string
     */
    protected $description = 'Envía una notificación de prueba vía Reverb al usuario indicado';

    /**
     * Ejecutar el comando
     */
    public function handle(): int
    {
        $email = $this->option('email');

        if (! $email) {
            $this->error('Debes indicar el parámetro --email.');

            return self::FAILURE;
        }

        if (config('broadcasting.default') === 'null') {
            $this->warn('BROADCAST_CONNECTION está en "null". La notificación se guardará en base de datos pero no se emitirá por Reverb.');
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No se encontró un usuario con el email [{$email}].");

            return self::FAILURE;
        }

        $user->notifyNow(new UserNotification(
            title: 'Notificación de prueba',
            description: 'Esta es una notificación enviada con notify:test para verificar Reverb.',
            message: 'Si ves este mensaje en tiempo real, Reverb está funcionando correctamente.',
            type: 'success',
            timeout: 30,
        ));

        $this->info("Notificación de prueba enviada a {$user->full_name} ({$user->email}).");

        if (config('broadcasting.default') === 'reverb') {
            $this->line('Canal privado: App.Models.User.'.$user->id);
            $this->line('Asegúrate de tener Reverb activo y VITE_REVERB_ACTIVE=true en el frontend.');
        }

        return self::SUCCESS;
    }
}
