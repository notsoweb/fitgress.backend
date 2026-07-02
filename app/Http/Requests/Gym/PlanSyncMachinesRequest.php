<?php

namespace App\Http\Requests\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sincronizar máquinas de un plan
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class PlanSyncMachinesRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('plans.edit');
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'machines' => ['required', 'array', 'min:1'],
            'machines.*.id' => ['required', 'integer', Rule::exists('gym_machines', 'id')],
            'machines.*.position' => ['required', 'integer', 'min:0'],
        ];
    }
}
