<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Ejercicio del gimnasio
 *
 * Un ejercicio puede estar asociado (opcionalmente) a una máquina. El tipo
 * efectivo se hereda de la máquina y puede ser sobrescrito por el ejercicio.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'machine_id',
    'name',
    'description',
    'type_ek',
])]
class Exercise extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_exercises';

    /**
     * Atributos calculados que se agregan a la serialización
     *
     * @var array<int, string>
     */
    protected $appends = [
        'effective_type',
    ];

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

    // Accessors

    /**
     * Tipo efectivo del ejercicio (propio o heredado de la máquina)
     */
    public function effectiveType(): ?MachineTypeEk
    {
        return $this->type_ek ?? $this->machine?->type_ek;
    }

    /**
     * Exponer el tipo efectivo como cadena en la serialización
     */
    public function getEffectiveTypeAttribute(): ?string
    {
        return $this->effectiveType()?->value;
    }

    // Relaciones

    /**
     * Máquina asociada al ejercicio (opcional)
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Propiedades variables del ejercicio
     * (altura del asiento, distancia, número de eje, etc.)
     */
    public function properties(): HasMany
    {
        return $this->hasMany(ExerciseProperty::class)->orderBy('position');
    }

    /**
     * Notas persistentes por usuario sobre el ejercicio
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ExerciseNote::class);
    }

    /**
     * Registros de entrenamiento sobre este ejercicio
     */
    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Planes donde está asignado el ejercicio
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'gym_plan_exercises')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Boot: eliminar propiedades y notas en cascada manual
     */
    protected static function booted(): void
    {
        static::deleting(function (Exercise $exercise) {
            $exercise->properties()->delete();
            $exercise->notes()->delete();
        });
    }
}
