<?php

namespace App\Http\Controllers\Resources;

/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Models\Machine;

/**
 * Recurso de máquina
 */
class MachineResource extends Controller
{
    /**
     * Listar todas las máquinas (catálogo sin paginar)
     *
     * @param  mixed  $data  Payload opcional enviado por ResourceController
     */
    public function all(mixed $data = null)
    {
        return Machine::query()->orderBy('name')->get(['id', 'name', 'code', 'type_ek']);
    }
}
