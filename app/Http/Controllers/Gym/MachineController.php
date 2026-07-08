<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\MachineStoreRequest;
use App\Http\Requests\Gym\MachineUpdateRequest;
use App\Models\Machine;
use App\Supports\QuerySupport;
use Illuminate\Routing\Controllers\HasMiddleware;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Máquinas del gimnasio (equipo)
 *
 * Requiere permisos `machines.*` (admin/developer). Las propiedades y notas
 * viven en el ejercicio, no en la máquina.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 2.0.0
 */
class MachineController extends Controller implements HasMiddleware
{
    /**
     * Middlewares
     */
    public static function middleware(): array
    {
        return [
            self::can('machines.index', ['index', 'show']),
            self::can('machines.create', ['store']),
            self::can('machines.edit', ['update']),
            self::can('machines.destroy', ['destroy']),
        ];
    }

    /**
     * Listar máquinas
     */
    public function index()
    {
        $models = Machine::orderBy('name');

        QuerySupport::queryByKeys($models, ['name', 'code', 'description']);

        return ApiResponse::OK->response([
            'models' => $models->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Almacenar máquina
     */
    public function store(MachineStoreRequest $request)
    {
        Machine::create($request->validated());

        return ApiResponse::CREATED->response();
    }

    /**
     * Mostrar máquina
     */
    public function show(Machine $machine)
    {
        return ApiResponse::OK->response([
            'model' => $machine,
        ]);
    }

    /**
     * Actualizar máquina
     */
    public function update(MachineUpdateRequest $request, Machine $machine)
    {
        $machine->update($request->validated());

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar máquina
     */
    public function destroy(Machine $machine)
    {
        $machine->delete();

        return ApiResponse::OK->response([
            'deleted' => true,
        ]);
    }
}
