<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Propiedad de una máquina del gimnasio
 *
 * Permite registrar atributos variables por máquina
 * (altura del asiento, distancia al pecho, número de eje, etc.).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'machine_id',
    'name',
    'value',
    'unit',
    'position',
])]
class MachineProperty extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_machine_properties';

    // Relaciones

    /**
     * Máquina a la que pertenece la propiedad
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
