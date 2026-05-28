<?php namespace App\Http\Controllers\System;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use Notsoweb\ApiResponse\Enums\ApiResponse;
use Spatie\Permission\Models\Permission;

/**
 * Permisos del sistema
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class PermissionController extends Controller
{
    //| Listar permisos del sistema
    public function index()
    {
        return ApiResponse::OK->response([
            'permissions' => Permission::orderBy('name')
                ->select('id', 'name', 'description')
                ->get()
        ]);
    }
}
