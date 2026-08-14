<?php

namespace App\Http\Controllers\Resources;

/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Models\Exercise;

/**
 * Recurso de ejercicio
 */
class ExerciseResource extends Controller
{
    /**
     * Listar todos los ejercicios (catálogo sin paginar)
     *
     * @param  mixed  $data  Payload opcional enviado por ResourceController
     */
    public function all(mixed $data = null)
    {
        return Exercise::with(['machine:id,name,code,type_ek', 'properties'])->orderBy('name')->get();
    }
}
