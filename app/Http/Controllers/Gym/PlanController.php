<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\PlanStoreRequest;
use App\Http\Requests\Gym\PlanSyncMachinesRequest;
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
            self::can('plans.index', ['index', 'show', 'machines']),
            self::can('plans.create', ['store']),
            self::can('plans.edit', ['update', 'syncMachines']),
            self::can('plans.destroy', ['destroy']),
        ];
    }

    /**
     * Listar planes
     */
    public function index()
    {
        $models = Plan::with('machines:id,name,code,type_ek')->orderBy('name');

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
            'model' => $plan->load('machines:id,name,code,type_ek', 'machines.properties'),
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
     * Máquinas de un plan (ordenadas por posición)
     */
    public function machines(Plan $plan)
    {
        return ApiResponse::OK->response([
            'machines' => $plan->machines()
                ->with('properties')
                ->get(['gym_machines.id', 'name', 'code', 'type_ek']),
        ]);
    }

    /**
     * Sincronizar máquinas del plan con sus posiciones
     */
    public function syncMachines(PlanSyncMachinesRequest $request, Plan $plan)
    {
        $sync = [];

        foreach ($request->input('machines', []) as $item) {
            $sync[$item['id']] = ['position' => $item['position']];
        }

        $plan->machines()->sync($sync);

        return ApiResponse::OK->response([
            'machines' => $plan->machines()
                ->with('properties')
                ->get(['gym_machines.id', 'name', 'code', 'type_ek']),
        ]);
    }
}
