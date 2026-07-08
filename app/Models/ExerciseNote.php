<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Nota persistente de un usuario sobre un ejercicio
 *
 * Cada usuario mantiene una única nota por ejercicio (única por
 * `user_id` + `exercise_id`).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'user_id',
    'exercise_id',
    'note',
])]
class ExerciseNote extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_exercise_notes';

    // Relaciones

    /**
     * Usuario autor de la nota
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ejercicio al que pertenece la nota
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
