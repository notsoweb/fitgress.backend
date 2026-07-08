<?php

namespace App\Http\Requests\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Models\Exercise;
use Illuminate\Validation\Validator;

/**
 * Validación de registros según el tipo efectivo del ejercicio
 *
 * Comparte las reglas base de métricas y la validación condicional entre las
 * solicitudes de creación y actualización de registros.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
trait ValidatesRegistroByType
{
    /**
     * Reglas base (sin requerir) para las métricas del registro
     *
     * @return array<string, mixed>
     */
    protected function metricRules(): array
    {
        return [
            'series' => ['nullable', 'integer', 'min:1', 'max:100'],
            'reps' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'distance' => ['nullable', 'numeric', 'min:0'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'incline' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Aplicar validación condicional según el tipo efectivo del ejercicio
     */
    protected function validateByType(Validator $validator, ?Exercise $exercise): void
    {
        if (! $exercise) {
            return;
        }

        $type = $exercise->effectiveType();

        [$required, $prohibited] = match ($type) {
            MachineTypeEk::REPS => [['series', 'reps'], ['weight', 'duration', 'distance', 'speed', 'incline']],
            MachineTypeEk::WEIGHT => [['series', 'reps', 'weight'], ['duration', 'distance', 'speed', 'incline']],
            MachineTypeEk::DISTANCE => [['duration', 'distance'], ['series', 'reps', 'weight']],
            MachineTypeEk::TIME => [['series', 'duration'], ['reps', 'weight', 'distance', 'speed', 'incline']],
            default => [[], []],
        };

        $validator->after(function (Validator $validator) use ($required, $prohibited) {
            foreach ($required as $field) {
                if (! $this->filled($field)) {
                    $validator->errors()->add($field, __('validation.required', ['attribute' => $field]));
                }
            }

            foreach ($prohibited as $field) {
                if ($this->filled($field)) {
                    $validator->errors()->add($field, __('validation.prohibited', ['attribute' => $field]));
                }
            }
        });
    }
}
