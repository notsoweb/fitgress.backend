<?php namespace App\Http\Requests\Roles;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Actualizar rol
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class RoleUpdateRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('roles.edit');
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', Rule::unique('roles')->ignore($this->route('role'))],
        ];
    }

    /**
     * Después de la validación
     */
    protected function passedValidation(): void
    {
        if (! in_array($this->route('role')->id, [1, 2])) {
            $this->merge([
                'name' => Str::slug($this->description),
            ]);
        }
    }
}
