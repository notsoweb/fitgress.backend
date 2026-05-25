<?php namespace App\Models;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Historial de eventos
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
#[Fillable([
    'event',
    'name',
    'data',
    'reportable_id',
    'reportable_type',
    'user_id'
 ])]
#[Appends([
    'description'
 ])]
class LogEvent extends Model
{
    /**
     * Desactivar fecha actualización
     */
    const UPDATED_AT = null;

    /**
     * Transformación de los datos
     */
    protected function casts(): array
    {
        return [
            'data' => 'json'
        ];
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Descripción del evento
     */
    public function description() : Attribute
    {
        return Attribute::make(
            get: fn() => __($this->event, ['model' => $this->name]),
        );
    }

    /**
     * Relación con el modelo reportable
     */
    public function reportable()
    {
        return $this->morphTo();
    }

    /**
     * Reportar un evento
     */
    public static function report(Model $model, string $event, string $key = 'name', bool $reportChanges = false)
    {
        $event = strtolower(explode('\\', get_class($model))[2]) . '.' . $event;

        self::create([
            'event' => $event,
            'name' => $model->{$key},
            'data' => $reportChanges ? $model->getContrastChanges() : $model->fillableToArray(),
            'reportable_id' => $model->id,
            'reportable_type' => get_class($model),
            'user_id' => auth()?->user()?->id
        ]);
    }
}
