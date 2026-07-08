<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Máquinas del gimnasio
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'name',
    'description',
    'code',
    'type_ek',
])]
class Machine extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_machines';

    /**
     * Conversión de tipos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type_ek' => MachineTypeEk::class,
        ];
    }

    // Relaciones

    /**
     * Ejercicios que utilizan esta máquina
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
}
