<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\ExerciseNoteRequest;
use App\Models\Exercise;
use App\Models\ExerciseNote;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Notas persistentes de ejercicios por usuario
 *
 * Cada usuario mantiene una nota única por ejercicio. No requiere permiso
 * Spatie: el acceso está acotado al usuario autenticado.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class ExerciseNoteController extends Controller
{
    /**
     * Mostrar la nota del usuario autenticado para un ejercicio
     */
    public function show(Exercise $exercise)
    {
        $note = ExerciseNote::query()
            ->where('user_id', auth()->id())
            ->where('exercise_id', $exercise->id)
            ->value('note');

        return ApiResponse::OK->response([
            'note' => $note,
        ]);
    }

    /**
     * Crear o actualizar la nota del usuario autenticado para un ejercicio
     */
    public function upsert(ExerciseNoteRequest $request, Exercise $exercise)
    {
        $note = $request->input('note');

        if (blank($note)) {
            ExerciseNote::query()
                ->where('user_id', auth()->id())
                ->where('exercise_id', $exercise->id)
                ->delete();

            return ApiResponse::OK->response([
                'note' => null,
            ]);
        }

        ExerciseNote::updateOrCreate(
            ['user_id' => auth()->id(), 'exercise_id' => $exercise->id],
            ['note' => $note],
        );

        return ApiResponse::OK->response([
            'note' => $note,
        ]);
    }
}
