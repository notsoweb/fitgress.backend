<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador base
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
abstract class Controller
{
    /**
     * Evaluar permisos de un usuario
     */
    public static function can(string $permission, array $methods): Middleware
    {
        return new Middleware("permission:{$permission}", only: $methods);
    }

    /**
     * Evaluar roles de un usuario
     */
    public static function role(string $role, array $methods): Middleware
    {
        return new Middleware("role:{$role}", only: $methods);
    }
}
