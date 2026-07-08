<?php

namespace App\Emums;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Notsoweb\LaravelCore\Traits\Enums\Extended;

/**
 * Tipos de configuración
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
enum SettingTypeEk: string
{
    use Extended;

    /**
     * Texto
     */
    case STRING = 'S';

    /**
     * JSON
     */
    case JSON = 'J';

    /**
     * Booleano
     */
    case BOOL = 'B';

    /**
     * Entero
     */
    case INT = 'I';
}
