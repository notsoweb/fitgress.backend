<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\PlanStoreRequest;
use App\Http\Requests\Gym\PlanSyncExercisesRequest;
use App\Http\Requests\Gym\PlanUpdateRequest;
use App\Models\Plan;
use App\Supports\QuerySupport;
use Illuminate\Routing\Controllers\HasMiddleware;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Planes de entrenamiento
 *
 * Requiere permisos `plans.*` (admin/developer).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class PlanController extends Controller implements HasMiddleware
{
    /**
     * Middlewares
     */
    public static function middleware(): array
    {
        return [
            self::can('plans.index', ['index', 'show', 'exercises']),
            self::can('plans.create', ['store']),
            self::can('plans.edit', ['update', 'syncExercises']),
            self::can('plans.destroy', ['destroy']),
        ];
    }

    /**
     * Listar planes
     */
    public function index()
    {
        $models = Plan::with(['exercises:id,machine_id,name,type_ek', 'exercises.machine:id,name,code,type_ek'])
            ->orderBy('name');

        QuerySupport::queryByKeys($models, ['name', 'description']);

        return ApiResponse::OK->response([
            'models' => $models->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Almacenar plan
     */
    public function store(PlanStoreRequest $request)
    {
        Plan::create($request->validated());

        return ApiResponse::CREATED->response();
    }

    /**
     * Mostrar plan
     */
    public function show(Plan $plan)
    {
        return ApiResponse::OK->response([
            'model' => $plan->load([
                'exercises:id,machine_id,name,description,type_ek',
                'exercises.machine:id,name,code,type_ek',
                'exercises.properties',
            ]),
        ]);
    }

    /**
     * Actualizar plan
     */
    public function update(PlanUpdateRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar plan
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return ApiResponse::OK->response([
            'deleted' => true,
        ]);
    }

    /**
     * Ejercicios de un plan (ordenados por posición)
     */
    public function exercises(Plan $plan)
    {
        return ApiResponse::OK->response([
            'exercises' => $this->loadExercises($plan),
        ]);
    }

    /**
     * Sincronizar ejercicios del plan con sus posiciones
     */
    public function syncExercises(PlanSyncExercisesRequest $request, Plan $plan)
    {
        $sync = [];

        foreach ($request->input('exercises', []) as $item) {
            $sync[$item['id']] = ['position' => $item['position']];
        }

        $plan->exercises()->sync($sync);

        return ApiResponse::OK->response([
            'exercises' => $this->loadExercises($plan),
        ]);
    }

    /**
     * Cargar los ejercicios del plan con máquina y propiedades
     */
    private function loadExercises(Plan $plan)
    {
        return $plan->exercises()
            ->with(['machine:id,name,code,type_ek', 'properties'])
            ->get(['gym_exercises.id', 'machine_id', 'name', 'description', 'type_ek']);
    }
}
