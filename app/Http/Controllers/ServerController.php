<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Controlador del servidor
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class ServerController extends Controller
{
    /**
     * Estado del servidor
     * 
     * Permite verificar que el servidor esta respondiendo.
     */
    public function status()
    {
        return ApiResponse::OK->response([
            "status" => "ok"
        ]);
    }

    /**
     * Versión
     * 
     * Permite obtener la versión del servidor.
     */
    public function version()
    {
        return ApiResponse::OK->response([
            "version" => config('app.version')
        ]);
    }
}
