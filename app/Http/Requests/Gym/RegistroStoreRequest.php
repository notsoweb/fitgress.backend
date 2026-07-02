<?php

namespace App\Http\Requests\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Crear registro de entrenamiento
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class RegistroStoreRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Configurar el validador después de las reglas base
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $machine = Machine::find($this->input('machine_id'));

            if (! $machine) {
                return;
            }

            if ($machine->type_ek === MachineTypeEk::WEIGHT && $this->filled('weight') === false) {
                $validator->errors()->add('weight', __('validation.required', ['attribute' => 'weight']));
            }

            if ($machine->type_ek === MachineTypeEk::TIME && $this->filled('weight') === true) {
                $validator->errors()->add('weight', __('gym.registros.weight_not_allowed'));
            }
        });
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer'],
            'machine_id' => ['required', 'integer', Rule::exists('gym_machines', 'id')],
            'plan_id' => ['nullable', 'integer', Rule::exists('gym_plans', 'id')],
            'series' => ['required', 'integer', 'min:1', 'max:100'],
            'reps' => ['required', 'integer', 'min:1', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'performed_at' => ['required', 'date'],
        ];
    }

    /**
     * Preparar los datos antes de la validación
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->id,
        ]);
    }
}
