<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotificationCollection;

/**
 * Notificaciones de usuario
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'id',
    'type',
    'data',
    'read_at',
    'is_closed',
    'user_id',
])]
class Notification extends Model
{
    use HasCollection;

    /**
     * Tipo de llave primaria
     */
    protected $keyType = 'string';

    /**
     * Desactivar auto-incremento
     */
    public $incrementing = false;

    /**
     * Tipo de colección
     */
    protected static string $collectionClass = DatabaseNotificationCollection::class;

    /**
     * Transformar atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // Relaciones

    /**
     * Relación polimórfica
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Una notificación pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Acciones

    /**
     * Marcar notificación como leída
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Marcar como cerrado
     *
     * Usado en casos donde se ignora la notificación y no se desea ver en
     * la vista de notificaciones.
     *
     * @return void
     */
    public function markAsClosed()
    {
        $this->forceFill(['is_closed' => true])->save();
    }

    /**
     * Marcar como no leído
     */
    public function markAsUnread(): void
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determinar si una notificación ha sido leída
     */
    public function read(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Determinar si una notificación no ha sido leída
     */
    public function unread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Filtrar notificaciones leídas
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }
}
