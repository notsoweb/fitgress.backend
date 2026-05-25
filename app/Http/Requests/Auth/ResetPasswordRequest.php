<?php namespace App\Http\Requests\Auth;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */


use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Solicitud de reseteo de contraseña
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'exists:password_reset_tokens,uuid'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Mensajes de validación
     */
    public function messages(): array
    {
        return [
            'token.exists' => __('auth.token.not_exists'),
        ];
    }
}
