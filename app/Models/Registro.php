<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Notsoweb\LaravelCore\Traits\Models\Extended;

/**
 * Registro de entrenamiento por sesión/máquina
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'user_id',
    'machine_id',
    'plan_id',
    'series',
    'reps',
    'weight',
    'performed_at',
])]
class Registro extends Model
{
    use Extended,
        HasFactory;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'gym_registros';

    /**
     * Conversión de tipos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'weight' => 'decimal:3',
        ];
    }

    // Scopes

    /**
     * Scope para filtrar registros del usuario indicado
     *
     * @param  int|null  $userId  Predeterminado al usuario autenticado
     */
    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        return $query->where('user_id', $userId ?? auth()->id());
    }

    // Relaciones

    /**
     * Usuario dueño del registro
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Máquina usada en el registro
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Plan al que pertenece el registro (opcional)
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
