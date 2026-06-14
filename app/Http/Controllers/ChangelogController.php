<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Illuminate\Http\Request;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Controlador de cambios del sistema
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class ChangelogController extends Controller
{
    /**
     * Cambios del sistema
     */
    public function __invoke()
    {
        return ApiResponse::OK->response(array_reverse([
            [
                'version' => '0.9.9',
                'date' => '2026-06-14',
                'changes' => [
                    'ADD: Notificaciones en tiempo real.',
                ]
            ]
        ]));
    }
}
