<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Observers\RoleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Notsoweb\LaravelCore\Traits\Models\Extended;
use Spatie\Permission\Models\Role as BaseModel;

/**
 * Roles del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[ObservedBy([
    RoleObserver::class,
])]
class Role extends BaseModel
{
    use Extended;
}
