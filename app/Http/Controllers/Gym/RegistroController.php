<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\RegistroStoreRequest;
use App\Http\Requests\Gym\RegistroUpdateRequest;
use App\Models\Machine;
use App\Models\Registro;
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
 * @version 1.0.0
 */
class RegistroController extends Controller
{
    /**
     * Listar registros del usuario autenticado
     */
    public function index(Request $request)
    {
        $models = Registro::forUser()
            ->with(['machine:id,name,code,type_ek', 'plan:id,name'])
            ->orderByDesc('performed_at');

        if ($request->filled('machine_id')) {
            $models->where('machine_id', $request->integer('machine_id'));
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
            'model' => $registro->load(['machine:id,name,code,type_ek', 'plan:id,name']),
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
     * Serie temporal para gráficas de progreso
     *
     * Query params:
     * - `machine_id` (required): máquina
     * - `metric` (optional): `weight|reps|series`. Si se omite se retornan todas las métricas combinadas.
     * - `from`, `to` (optional): rango de fechas
     */
    public function charts(Request $request)
    {
        $request->validate([
            'machine_id' => ['required', 'integer', 'exists:gym_machines,id'],
            'metric' => ['nullable', 'in:weight,reps,series'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $machine = Machine::findOrFail($request->integer('machine_id'), ['id', 'name', 'code', 'type_ek']);

        $rows = Registro::forUser()
            ->where('machine_id', $request->integer('machine_id'))
            ->when($request->filled('from'), fn (Builder $q) => $q->where('performed_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->where('performed_at', '<=', $request->input('to')))
            ->orderBy('performed_at')
            ->get(['performed_at', 'series', 'reps', 'weight']);

        $metrics = $request->filled('metric')
            ? [$request->input('metric')]
            : array_filter(['series', 'reps', 'weight'], fn (string $m) => $m !== 'weight' ? true : $machine->type_ek === MachineTypeEk::WEIGHT);

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
            'machine' => $machine,
            'metrics' => $metrics,
            'series' => $series,
        ]);
    }
}
