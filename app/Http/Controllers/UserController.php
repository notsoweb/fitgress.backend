<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Requests\User\ConfirmPasswordRequest;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Usuario
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class UserController extends Controller
{
    /**
     * Detalles
     */
    public function show()
    {
        return ApiResponse::OK->response([
            'model' => Auth::user()
        ]);
    }

    /**
     * Actualizar
     */
    public function update(UpdateRequest $request)
    {
        $form = $request->validated();

        if (isset($form['photo']) && !empty($form['photo'])) {
            Auth::user()->updateProfilePhoto($form['photo']);
        }

        return ApiResponse::OK->response([
            'updated' => Auth::user()->update($form)
        ]);
    }

    /**
     * Eliminar
     */
    public function destroy()
    {
        return ApiResponse::OK->response([
            'deleted' => Auth::user()->delete()
        ]);
    }

    /**
     * Confirmar contraseña
     */
    public function confirmPassword(PasswordRequest $request)
    {
        if(!Hash::check($request->password, Auth::user()->password)) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'message' => __('passwords.error')
            ]);
        }

        return ApiResponse::OK->response();
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        // Evaluar contraseña actual
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'message' => __('validation.password')
            ]);
        }

        return ApiResponse::OK->response([
            'updated' => Auth::user()->forcePassword($request->password)
        ]);
    }

    /**
     * Eliminar foto de perfil
     */
    public function destroyPhoto()
    {
        Auth::user()->deleteProfilePhoto();

        return ApiResponse::OK->response();
    }

    /**
     * Permisos 
     */
    public function permissions()
    {
        return ApiResponse::OK->response([
            'permissions' => Auth::user()->getAllPermissions()
        ]);
    }

    /**
     * Roles
     */
    public function roles()
    {
        return ApiResponse::OK->response([
            'roles' => Auth::user()->roles()
                ->select('id', 'name', 'description')
                ->get()
        ]);
    }
}
