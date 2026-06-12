<?php namespace App\Http\Requests\Activity;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtrar historial de actividades
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class ActivityIndexRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return $this->user()->can('activities.index');
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'user' => ['nullable', 'exists:users,id'],
        ];
    }
}
