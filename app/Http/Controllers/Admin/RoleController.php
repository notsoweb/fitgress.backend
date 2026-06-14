<?php namespace App\Http\Controllers\Admin;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Events\Roles\PermissionUpdate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\RoleStoreRequest;
use App\Http\Requests\Roles\RoleUpdateRequest;
use App\Models\Role;
use App\Supports\QuerySupport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Roles del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class RoleController extends Controller implements HasMiddleware
{
    /**
     * Middlewares
     */
    public static function middleware(): array
    {
        return [
            self::can('roles.index', ['index', 'show']),
            self::can('roles.create', ['store']),
            self::can('roles.edit', ['permissions', 'updatePermissions']),
            self::can('roles.destroy', ['destroy']),
        ];
    }

    /**
     * Listar roles
     */
    public function index()
    {
        $model = Role::orderBy('description');

        if (! Auth::user()->isDeveloper()) {
            $model->whereNot('id', 1);
        }

        QuerySupport::queryByKeys($model, ['description', 'name']);

        return ApiResponse::OK->response([
            'models' => $model->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Almacenar rol
     */
    public function store(RoleStoreRequest $request)
    {
        Role::create($request->all());

        return ApiResponse::OK->response();
    }

    /**
     * Mostrar rol
     */
    public function show(Role $role)
    {
        return ApiResponse::OK->response([
            'model' => $role,
        ]);
    }

    /**
     * Actualizar rol
     */
    public function update(RoleUpdateRequest $request, Role $role)
    {
        $role->update($request->all());

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar rol
     */
    public function destroy(Role $role)
    {
        if (in_array($role->id, [1, 2])) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'message' => __('roles.cannot_delete_primary'),
            ]);
        }

        $role->delete();

        return ApiResponse::OK->response();
    }

    /**
     * Permisos de un rol
     */
    public function permissions(Role $role)
    {
        return ApiResponse::OK->response([
            'permissions' => $role->permissions,
        ]);
    }

    /**
     * Actualizar permisos de un rol
     */
    public function updatePermissions(Role $role, Request $request)
    {
        $role->syncPermissions($request->input('permissions', []));

        PermissionUpdate::dispatch($role);

        return ApiResponse::OK->response();
    }
}
