<?php namespace App\Http\Controllers\System;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Events\Roles\PermissionUpdate;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Roles del sistema
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class RoleController extends Controller
{
    //| Listar roles del sistema
    public function index()
    {
        return ApiResponse::OK->response([
            'roles' => Role::orderBy('description')
                ->select('id', 'name', 'description')
                ->get()
        ]);
    }

    /**
     * Permisos de un rol
     */
    public function permissions(Role $role)
    {
        return ApiResponse::OK->response([
            'permissions' => $role->permissions
        ]);
    }

    /**
     * Actualizar permisos de un rol
     */
    public function updatePermissions(Role $role, Request $request)
    {
        $role->syncPermissions($request->input('permissions', []));

        // Notificar a los usuarios que tienen este rol
        PermissionUpdate::dispatch($role);

        return ApiResponse::OK->response();
    }
}
