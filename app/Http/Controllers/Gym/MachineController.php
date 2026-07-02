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
 * Máquinas del gimnasio
 *
 * Requiere permisos `machines.*` (admin/developer).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
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
        $models = Machine::with('properties')->orderBy('name');

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
        $data = $request->safe()->except('properties');

        $machine = Machine::create($data);

        $this->syncProperties($machine, $request->input('properties', []));

        return ApiResponse::CREATED->response();
    }

    /**
     * Mostrar máquina
     */
    public function show(Machine $machine)
    {
        return ApiResponse::OK->response([
            'model' => $machine->load('properties'),
        ]);
    }

    /**
     * Actualizar máquina
     */
    public function update(MachineUpdateRequest $request, Machine $machine)
    {
        $data = $request->safe()->except('properties');

        $machine->update($data);

        $this->syncProperties($machine, $request->input('properties', []));

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

    /**
     * Sincronizar propiedades de la máquina
     *
     * Reemplaza las propiedades existentes por las enviadas en la solicitud.
     *
     * @param  array<int, array{name: string, value: ?string, unit: ?string}>  $properties
     */
    private function syncProperties(Machine $machine, array $properties): void
    {
        $machine->properties()->delete();

        if (empty($properties)) {
            return;
        }

        $records = array_map(fn (array $item, int $index) => [
            'machine_id' => $machine->id,
            'name' => $item['name'],
            'value' => $item['value'] ?? null,
            'unit' => $item['unit'] ?? null,
            'position' => $index,
            'created_at' => now(),
            'updated_at' => now(),
        ], $properties, array_keys($properties));

        $machine->properties()->createMany($records);
    }
}
