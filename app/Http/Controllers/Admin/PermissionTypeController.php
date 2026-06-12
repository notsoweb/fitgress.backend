<?php namespace App\Http\Controllers\Admin;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Tipos de permisos
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class PermissionTypeController extends Controller implements HasMiddleware
{
    /**
     * Middlewares
     */
    public static function middleware(): array
    {
        return [
            self::can('roles.index', ['allWithPermissions']),
        ];
    }

    /**
     * Listar tipos de permisos con sus permisos
     */
    public function allWithPermissions()
    {
        return ApiResponse::OK->response([
            'models' => PermissionType::with('permissions')->orderBy('name')->get(),
        ]);
    }
}
