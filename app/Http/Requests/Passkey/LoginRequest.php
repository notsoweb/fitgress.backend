<?php namespace App\Http\Requests\Passkey;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;

/**
 * Solicitud de login con passkey
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class LoginRequest extends AuthenticateUsingPasskeysRequest
{
    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'options' => ['nullable', 'json'],
        ];
    }
}
