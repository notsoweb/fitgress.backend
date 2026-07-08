<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\RegistroStoreRequest;
use App\Http\Requests\Gym\RegistroUpdateRequest;
use App\Models\Exercise;
use App\Models\ExerciseNote;
use App\Models\Registro;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Registros de entrenamiento del usuario autenticado
 *
 * Cada usuario solo puede ver y gestionar sus propios registros.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 2.0.0
 */
class RegistroController extends Controller
{
    /**
     * Métricas disponibles para las gráficas de progreso
     *
     * @var array<int, string>
     */
    private const METRICS = ['series', 'reps', 'weight', 'duration', 'distance', 'speed', 'incline'];

    /**
     * Listar registros del usuario autenticado
     */
    public function index(Request $request)
    {
        $models = Registro::forUser()
            ->with(['exercise:id,machine_id,name,type_ek', 'exercise.machine:id,name,code,type_ek', 'plan:id,name'])
            ->orderByDesc('performed_at');

        if ($request->filled('exercise_id')) {
            $models->where('exercise_id', $request->integer('exercise_id'));
        }

        if ($request->filled('plan_id')) {
            $models->where('plan_id', $request->integer('plan_id'));
        }

        if ($request->filled('from')) {
            $models->where('performed_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $models->where('performed_at', '<=', $request->input('to'));
        }

        return ApiResponse::OK->response([
            'models' => $models->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Almacenar registro
     */
    public function store(RegistroStoreRequest $request)
    {
        Registro::create($request->validated());

        return ApiResponse::CREATED->response();
    }

    /**
     * Mostrar registro
     */
    public function show(Registro $registro)
    {
        if ($registro->user_id !== auth()->id()) {
            return ApiResponse::FORBIDDEN->response();
        }

        return ApiResponse::OK->response([
            'model' => $registro->load([
                'exercise:id,machine_id,name,type_ek',
                'exercise.machine:id,name,code,type_ek',
                'plan:id,name',
            ]),
        ]);
    }

    /**
     * Actualizar registro
     */
    public function update(RegistroUpdateRequest $request, Registro $registro)
    {
        $registro->update($request->validated());

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar registro
     */
    public function destroy(Registro $registro)
    {
        if ($registro->user_id !== auth()->id()) {
            return ApiResponse::FORBIDDEN->response();
        }

        $registro->delete();

        return ApiResponse::OK->response([
            'deleted' => true,
        ]);
    }

    /**
     * Último registro del usuario para un ejercicio + su nota persistente
     *
     * Query params:
     * - `exercise_id` (required): ejercicio
     */
    public function last(Request $request)
    {
        $request->validate([
            'exercise_id' => ['required', 'integer', 'exists:gym_exercises,id'],
        ]);

        $exerciseId = $request->integer('exercise_id');

        $model = Registro::forUser()
            ->where('exercise_id', $exerciseId)
            ->orderByDesc('performed_at')
            ->first();

        $note = ExerciseNote::query()
            ->where('user_id', auth()->id())
            ->where('exercise_id', $exerciseId)
            ->value('note');

        return ApiResponse::OK->response([
            'model' => $model,
            'note' => $note,
        ]);
    }

    /**
     * Ejercicios del plan que ya tienen registro en la fecha indicada
     *
     * Query params:
     * - `plan_id` (required): plan seleccionado
     * - `date` (required): día en formato Y-m-d
     */
    public function session(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:gym_plans,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = CarbonImmutable::createFromFormat('Y-m-d', $validated['date'])->toDateString();

        $completedExerciseIds = Registro::forUser()
            ->where('plan_id', $validated['plan_id'])
            ->whereDate('performed_at', $date)
            ->distinct()
            ->orderBy('exercise_id')
            ->pluck('exercise_id')
            ->map(fn ($exerciseId) => (int) $exerciseId)
            ->all();

        return ApiResponse::OK->response([
            'completed_exercise_ids' => $completedExerciseIds,
        ]);
    }

    /**
     * Serie temporal para gráficas de progreso
     *
     * Query params:
     * - `exercise_id` (required): ejercicio
     * - `metric` (optional): una de `series,reps,weight,duration,distance,speed,incline`.
     *   Si se omite se retornan las métricas por defecto del tipo efectivo.
     * - `from`, `to` (optional): rango de fechas
     */
    public function charts(Request $request)
    {
        $request->validate([
            'exercise_id' => ['required', 'integer', 'exists:gym_exercises,id'],
            'metric' => ['nullable', 'in:'.implode(',', self::METRICS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $exercise = Exercise::with('machine:id,name,code,type_ek')
            ->findOrFail($request->integer('exercise_id'), ['id', 'machine_id', 'name', 'type_ek']);

        $rows = Registro::forUser()
            ->where('exercise_id', $exercise->id)
            ->when($request->filled('from'), fn (Builder $q) => $q->where('performed_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->where('performed_at', '<=', $request->input('to')))
            ->orderBy('performed_at')
            ->get(array_merge(['performed_at'], self::METRICS));

        $metrics = $request->filled('metric')
            ? [$request->input('metric')]
            : $this->defaultMetrics($exercise->effectiveType());

        $series = array_map(function (string $metric) use ($rows) {
            return [
                'metric' => $metric,
                'data' => $rows->map(fn (Registro $r) => [
                    'performed_at' => $r->performed_at->toIso8601String(),
                    'value' => $r->{$metric} === null ? null : (float) $r->{$metric},
                ])->all(),
            ];
        }, $metrics);

        return ApiResponse::OK->response([
            'exercise' => $exercise,
            'metrics' => $metrics,
            'series' => $series,
        ]);
    }

    /**
     * Métricas por defecto según el tipo efectivo del ejercicio
     *
     * @return array<int, string>
     */
    private function defaultMetrics(?MachineTypeEk $type): array
    {
        return match ($type) {
            MachineTypeEk::WEIGHT => ['series', 'reps', 'weight'],
            MachineTypeEk::DISTANCE => ['duration', 'distance', 'speed', 'incline'],
            MachineTypeEk::TIME => ['series', 'duration'],
            default => ['series', 'reps'],
        };
    }
}
