<?php namespace App\Http\Controllers\Admin;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Events\Users\RoleUpdate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Http\Requests\User\PasswordResetRequest;
use App\Models\User;
use App\Supports\QuerySupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Controlador de usuarios
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class UserController extends Controller
{
    /**
     * Listar usuarios
     */
    public function index()
    {
        $users = User::orderBy('name');

        QuerySupport::queryByKeys($users, ['name', 'email']);

        if(!Auth::user()->isDeveloper()) {
            $users->whereNotIn('id', [1]);
        }

        return ApiResponse::OK->response([
            'models' => $users->paginate(config('app.pagination'))
        ]);
    }

    /**
     * Almacenar usuario
     */
    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->validated());

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return ApiResponse::OK->response();
    }

    /**
     * Mostrar usuario
     */
    public function show(User $user)
    {
        return ApiResponse::OK->response([
            'model' => $user
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update($request->validated());

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        // Evitar eliminar usuarios primarios
        if($user->isPrimary()) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'message' => __('users.cannot_delete_primary')
            ]);
        }

        $user->delete();

        return ApiResponse::OK->response([
            'deleted' => $user->trashed()
        ]);
    }

    /**
     * Permisos del usuario
     */
    public function permissions(User $user)
    {
        return ApiResponse::OK->response([
            'permissions' => $user->getAllPermissions()
        ]);
    }

    /**
     * Roles del usuario
     */
    public function roles(User $user)
    {
        return ApiResponse::OK->response([
            'roles' => $user->roles()
                ->select('id', 'name', 'description')
                ->get()
        ]);
    }

    /**
     * Actualizar roles
     */
    public function updateRoles(Request $request, User $user)
    {
        if ($request->has('roles')) {
            $toSync = [];

            // Verificar roles
            foreach ($request->roles as $role) {
                // Solo un desarrollador puede asignar el role de desarrollador
                if($role == 1 && Auth::user()->isDeveloper()) {
                    $toSync[] = $role;

                    continue;
                }

                // Solo un administrador puede asignar el role de administrador
                if($role == 2 && Auth::user()->hasRole([1,2])) {
                    $toSync[] = $role;

                    continue;
                }

                $toSync[] = $role;
            }

            $user->roles()->sync($toSync);

            RoleUpdate::dispatch($user);
        }

        return ApiResponse::OK->response();
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(PasswordResetRequest $request, User $user)
    {
        $user->forcePassword($request->password);

        return ApiResponse::OK->response();
    }
}
