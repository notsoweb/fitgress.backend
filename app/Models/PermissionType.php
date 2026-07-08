<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Notsoweb\LaravelCore\Traits\Models\Extended;
use Spatie\Permission\Models\Permission;

/**
 * Tipos de permisos
 *
 * Agrupa permisos bajo un nombre común.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'name',
    'description',
])]
class PermissionType extends Model
{
    use Extended;

    // Relaciones

    /**
     * Un tipo de permiso tiene muchos permisos
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
