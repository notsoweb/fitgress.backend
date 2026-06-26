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
                ],
            ],
            [
                'version' => '0.9.10',
                'date' => '2026-06-18',
                'changes' => [
                    'ADD: Backup automático y manual del sistema.',
                    'ADD: Notificación por correo electrónico cuando el backup falla o tiene éxito.',
                    'ADD: Comando para enviar un correo de prueba.',
                ]
            ],
            [
                'version' => '0.9.11',
                'date' => '2026-06-25',
                'changes' => [
                    'ADD: Notificaciones de backup automático y manual por discord.',
                    'ADD: Configuración de R2 de Cloudflare como disco de backup.',
                ],
            ],
        ]));
    }
}
