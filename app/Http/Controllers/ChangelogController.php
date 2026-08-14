<?php

namespace App\Http\Controllers;

/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

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
                ],
            ],
            [
                'version' => '0.9.11',
                'date' => '2026-06-25',
                'changes' => [
                    'ADD: Notificaciones de backup automático y manual por discord.',
                    'ADD: Configuración de R2 de Cloudflare como disco de backup.',
                ],
            ],
            [
                'version' => '0.9.12',
                'date' => '2026-06-27',
                'changes' => [
                    'ADD: Creación de rules y skills para agentes de IA.',
                    'ADD: Integración con codebase memory MCP (para desarrollo).',
                    'UPDATE: Actualización de documentación.',
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '2026-08-13',
                'changes' => [
                    'ADD: Dashboard del gimnasio: resumen de máquinas, ejercicios, días de entrenamiento y ejercicio más realizado.',
                    'ADD: Calendario de días entrenados con el plan ejecutado en cada fecha.',
                    'ADD: Catálogos dinámicos exercise:all y machine:all (POST /api/catalogs/get).',
                    'ADD: Filtros de máquina y tipo (incluido sin tipo) en el listado de ejercicios.',
                ],
            ],
        ]));
    }
}
