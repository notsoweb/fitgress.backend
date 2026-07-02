<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Planes de entrenamiento
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'name',
    'description',
])]
class Plan extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_plans';

    // Relaciones

    /**
     * Máquinas vinculadas al plan (ordenadas por posición)
     */
    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'gym_plan_machines')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Registros asociados al plan
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }
}
