<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * Propiedades variables de la máquina
     * (altura del asiento, distancia, número de eje, etc.)
     */
    public function properties()
    {
        return $this->hasMany(MachineProperty::class)->orderBy('position');
    }

    /**
     * Planes donde está asignada la máquina
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'gym_plan_machines')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Registros de entrenamiento sobre esta máquina
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Boot: eliminar propiedades en cascada manual
     */
    protected static function booted(): void
    {
        static::deleting(function (Machine $machine) {
            $machine->properties()->delete();
        });
    }
}
