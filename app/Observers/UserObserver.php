<?php namespace App\Observers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Models\LogEvent;
use App\Models\User;

/**
 * Observador del modelo User
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class UserObserver
{
    /**
     * Evento de creación
     */
    public function created(User $user): void
    {
        LogEvent::report(
            model: $user,
            event: __FUNCTION__,
            key: 'email'
        );
    }

    /**
     * Evento de actualización
     */
    public function updated(User $user): void
    {
        LogEvent::report(
            model: $user,
            event: __FUNCTION__,
            key: 'email',
            reportChanges: true
        );
    }

    /**
     * Evento de eliminación
     */
    public function deleted(User $user): void
    {
        LogEvent::report(
            model: $user,
            event: __FUNCTION__,
            key: 'email'
        );
    }

    /**
     * Evento de restauración
     */
    public function restored(User $user): void
    {
        LogEvent::report(
            model: $user,
            event: __FUNCTION__,
            key: 'email'
        );
    }

    /**
     * Evento de eliminación forzada
     */
    public function forceDeleted(User $user): void
    {
        LogEvent::report(
            model: $user,
            event: __FUNCTION__,
            key: 'email'
        );
    }
}
