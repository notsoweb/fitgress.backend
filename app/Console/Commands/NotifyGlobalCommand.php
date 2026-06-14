<?php namespace App\Console\Commands;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Events\Users\GlobalNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Enviar notificación global
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Signature('notify:global
        {--message=Notificación de prueba : Mensaje de la notificación}
        {--title=Notificación Global : Título de la notificación}
        {--type=info : Tipo de notificación (info, success, warning, error)}
        {--timeout=15 : Tiempo de duración de la notificación}')]
#[Description('Enviar notificación a todos los usuarios conectados')]
class NotifyGlobalCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        GlobalNotification::dispatch(
            $this->option('title'),
            $this->option('message'),
            $this->option('type'),
            $this->option('timeout')
        );
        
        return Command::SUCCESS;
    }
}
