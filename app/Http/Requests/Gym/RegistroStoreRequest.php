<?php

namespace App\Http\Requests\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Crear registro de entrenamiento
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 2.0.0
 */
class RegistroStoreRequest extends FormRequest
{
    use ValidatesRegistroByType;

    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Configurar el validador según el tipo efectivo del ejercicio
     */
    public function withValidator(Validator $validator): void
    {
        $this->validateByType($validator, Exercise::find($this->input('exercise_id')));
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'user_id' => ['sometimes', 'integer'],
            'exercise_id' => ['required', 'integer', Rule::exists('gym_exercises', 'id')],
            'plan_id' => ['nullable', 'integer', Rule::exists('gym_plans', 'id')],
            'performed_at' => ['required', 'date'],
        ], $this->metricRules());
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
