<?php

namespace App\Http\Requests\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Crear máquina del gimnasio
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class MachineStoreRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('machines.create');
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'code' => ['required', 'string', 'max:100', Rule::unique('gym_machines', 'code')],
            'type_ek' => ['required', 'string', Rule::in(MachineTypeEk::values())],
            'properties' => ['nullable', 'array'],
            'properties.*.name' => ['required', 'string', 'max:100'],
            'properties.*.value' => ['nullable', 'string', 'max:100'],
            'properties.*.unit' => ['nullable', 'string', 'max:20'],
        ];
    }
}
