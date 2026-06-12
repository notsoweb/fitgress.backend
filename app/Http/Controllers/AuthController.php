<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\Auth\ForgotPasswordNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Controlador de autenticación
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class AuthController extends Controller
{
    /**
     * Iniciar sesión
     */
    public function login(LoginRequest $request)
    {
        $user = User::select('id', 'name', 'password', 'paternal', 'maternal', 'email')
            ->where('email', $request->email)
            ->first();

        // Validad existencia y contraseña
        if (!$user || !$user->validatePassword($request->password)) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'email' => [__('auth.failed')],
            ]);
        }

        return ApiResponse::OK->response([
            'user' => $user,
            'token' => $user->createToken(config('app.slug'))->accessToken,
        ]);
    }

    /**
     * Contraseña olvidada
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $token = $user->generatePasswordResetToken();

        try {
            $user->notify(new ForgotPasswordNotification($token));

            return ApiResponse::OK->response([
                'is_sent' => true
            ]);
        } catch (\Throwable $th) {
            Log::channel('mail')->info("Email: {$request->email}");
            Log::channel('mail')->error($th->getMessage());

            $user->deletePasswordResetToken($token);

            return ApiResponse::INTERNAL_ERROR->response([
                'is_sent' => false,
            ]);
        }
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $resetToken = PasswordResetToken::where('uuid', $request->token)->first();
        
        // Calcular fecha de expiración
        $expires = $resetToken->created_at->addMinutes(250);

        if($expires < now()){
            // Eliminar token
            $resetToken->delete();

            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'token' => __('auth.token.expired')
            ]);
        }

        // Forzar contraseña
        $resetToken->user->forcePassword($request->password);

        // Eliminar token
        $resetToken->delete();

        return ApiResponse::OK->response([
            'is_updated' => true,
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        return ApiResponse::OK->response([
            'is_revoked' => Auth::user()->token()->revoke()
        ]);
    }
}
