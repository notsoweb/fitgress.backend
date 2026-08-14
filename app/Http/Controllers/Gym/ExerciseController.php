<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\ExerciseStoreRequest;
use App\Http\Requests\Gym\ExerciseUpdateRequest;
use App\Models\Exercise;
use App\Supports\QuerySupport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Ejercicios del gimnasio
 *
 * Requiere permisos `exercises.*` (admin/developer).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class ExerciseController extends Controller implements HasMiddleware
{
    /**
     * Middlewares
     */
    public static function middleware(): array
    {
        return [
            self::can('exercises.index', ['index', 'show']),
            self::can('exercises.create', ['store']),
            self::can('exercises.edit', ['update']),
            self::can('exercises.destroy', ['destroy']),
        ];
    }

    /**
     * Listar ejercicios
     *
     * Query params: `q`, `machine_id` (id o `none`), `type_ek` (R/W/D/T o `none`).
     * `type_ek` filtra por tipo efectivo (propio o heredado de la máquina).
     */
    public function index(Request $request)
    {
        $models = Exercise::with(['machine:id,name,code,type_ek', 'properties'])->orderBy('name');

        QuerySupport::queryByKeys($models, ['name', 'description', 'machine.name', 'machine.code']);

        $this->applyFilters($models, $request);

        return ApiResponse::OK->response([
            'models' => $models->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Almacenar ejercicio
     */
    public function store(ExerciseStoreRequest $request)
    {
        $data = $request->safe()->except('properties');

        $exercise = Exercise::create($data);

        $this->syncProperties($exercise, $request->input('properties', []));

        return ApiResponse::CREATED->response();
    }

    /**
     * Mostrar ejercicio
     */
    public function show(Exercise $exercise)
    {
        return ApiResponse::OK->response([
            'model' => $exercise->load(['machine:id,name,code,type_ek', 'properties']),
        ]);
    }

    /**
     * Actualizar ejercicio
     */
    public function update(ExerciseUpdateRequest $request, Exercise $exercise)
    {
        $data = $request->safe()->except('properties');

        $exercise->update($data);

        $this->syncProperties($exercise, $request->input('properties', []));

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar ejercicio
     */
    public function destroy(Exercise $exercise)
    {
        $exercise->delete();

        return ApiResponse::OK->response([
            'deleted' => true,
        ]);
    }

    /**
     * Sincronizar propiedades del ejercicio
     *
     * Reemplaza las propiedades existentes por las enviadas en la solicitud.
     *
     * @param  array<int, array{name: string, value: ?string, unit: ?string}>  $properties
     */
    private function syncProperties(Exercise $exercise, array $properties): void
    {
        $exercise->properties()->delete();

        if (empty($properties)) {
            return;
        }

        $records = array_map(fn (array $item, int $index) => [
            'exercise_id' => $exercise->id,
            'name' => $item['name'],
            'value' => $item['value'] ?? null,
            'unit' => $item['unit'] ?? null,
            'position' => $index,
            'created_at' => now(),
            'updated_at' => now(),
        ], $properties, array_keys($properties));

        $exercise->properties()->createMany($records);
    }

    /**
     * Filtros de listado: máquina y tipo efectivo
     */
    private function applyFilters(Builder $models, Request $request): void
    {
        if ($request->filled('machine_id')) {
            if ($request->input('machine_id') === 'none') {
                $models->whereNull('machine_id');
            } else {
                $models->where('machine_id', $request->integer('machine_id'));
            }
        }

        if (! $request->filled('type_ek')) {
            return;
        }

        $type = $request->input('type_ek');

        if ($type === 'none') {
            $models->whereNull('type_ek')->whereNull('machine_id');

            return;
        }

        if (! in_array($type, MachineTypeEk::values(), true)) {
            return;
        }

        $models->where(function (Builder $query) use ($type) {
            $query->where('type_ek', $type)
                ->orWhere(function (Builder $query) use ($type) {
                    $query->whereNull('type_ek')
                        ->whereHas('machine', fn (Builder $machine) => $machine->where('type_ek', $type));
                });
        });
    }
}
