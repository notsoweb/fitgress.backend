<?php namespace App\Http\Controllers\System;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use App\Supports\QuerySupport;
use Illuminate\Http\Request;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Controlador de tipos de permisos
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class PermissionTypeController extends Controller
{
    /**
     * Listar tipos de permisos
     */
    public function index(Request $request)
    {
        $model = PermissionType::orderBy('name');

        if($request->has('with_permissions')) {
            $model->with('permissions');
        }

        QuerySupport::queryByKeys($model, $request->all());

        return ApiResponse::OK->response([
            'models' => $model->get()
        ]);
    }
}
