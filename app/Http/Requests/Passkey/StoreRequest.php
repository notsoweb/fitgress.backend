<?php namespace App\Http\Requests\Passkey;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Solicitud de registro de passkey
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class StoreRequest extends FormRequest
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
            'passkey' => ['required', 'json'],
            'options' => ['required', 'json'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
