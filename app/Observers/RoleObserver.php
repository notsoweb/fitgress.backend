<?php namespace App\Observers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Models\LogEvent;
use App\Models\Role;

/**
 * Observador del modelo Role
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class RoleObserver
{
    /**
     * Evento de creación
     */
    public function created(Role $role): void
    {
        LogEvent::report(
            model: $role,
            event: __FUNCTION__,
            key: 'name'
        );
    }

    /**
     * Evento de actualización
     */
    public function updated(Role $role): void
    {
        LogEvent::report(
            model: $role,
            event: __FUNCTION__,
            key: 'name',
            reportChanges: true
        );
    }

    /**
     * Evento de eliminación
     */
    public function deleted(Role $role): void
    {
        LogEvent::report(
            model: $role,
            event: __FUNCTION__,
            key: 'name'
        );
    }

    /**
     * Evento de restauración
     */
    public function restored(Role $role): void
    {
        LogEvent::report(
            model: $role,
            event: __FUNCTION__,
            key: 'name'
        );
    }

    /**
     * Evento de eliminación forzada
     */
    public function forceDeleted(Role $role): void
    {
        LogEvent::report(
            model: $role,
            event: __FUNCTION__,
            key: 'name'
        );
    }
}
