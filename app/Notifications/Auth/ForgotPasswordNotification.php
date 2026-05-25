<?php namespace App\Notifications\Auth;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación de recuperación de contraseña
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class ForgotPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Crear instancia de la notificación
     */
    public function __construct(
        public string $token
    ) {}

    /**
     * Obtener los canales de entrega de la notificación
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtener la representación del mensaje de la notificación
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth.forgot.subject'))
            ->markdown('auth.forgot-password', [
                'user' => $notifiable,
                'token' => $this->token
            ]);
    }
}
