<?php

namespace App\Emums;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Notsoweb\LaravelCore\Traits\Enums\Extended;

/**
 * Tipos de máquina del gimnasio
 *
 * - `TIME`: el avance se mide por tiempo (ej. caminta, bicicleta).
 * - `WEIGHT`: el avance se mide por peso levantado.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
enum MachineTypeEk: string
{
    use Extended;

    /**
     * Máquina de tiempo
     */
    case TIME = 'T';

    /**
     * Máquina de peso
     */
    case WEIGHT = 'W';
}
