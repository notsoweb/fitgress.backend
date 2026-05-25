<?php namespace App\Models;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Token de reseteo de contraseña
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
#[Fillable([
    'user_id',
 ])]
class PasswordResetToken extends Model
{
    /**
     * Configuración del modelo
     */
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    
    /**
     * Desactivar fecha actualización
     */
    const UPDATED_AT = null;

    /**
     * Modelo iniciado
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid7()->toString();
        });
    }

    # Relaciones

    /**
     * Un token de reseteo de contraseña pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
