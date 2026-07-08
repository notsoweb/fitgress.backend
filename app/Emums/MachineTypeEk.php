<?php

namespace App\Emums;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use Notsoweb\LaravelCore\Traits\Enums\Extended;

/**
 * Tipos de ejercicio/máquina del gimnasio
 *
 * Define cómo se mide el avance de un ejercicio. El tipo puede vivir en la
 * máquina (tipo nativo) y ser sobrescrito por el ejercicio.
 *
 * - `REPS`: avance por series y repeticiones (ej. lagartijas, abdominales).
 * - `WEIGHT`: avance por peso levantado, además de series y repeticiones.
 * - `DISTANCE`: avance por duración y distancia (ej. caminadora, bicicleta).
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 2.0.0
 */
enum MachineTypeEk: string
{
    use Extended;

    /**
     * Ejercicio de repeticiones (series + reps)
     */
    case REPS = 'R';

    /**
     * Ejercicio de fuerza (series + reps + peso)
     */
    case WEIGHT = 'W';

    /**
     * Ejercicio de distancia (duración + distancia + velocidad/inclinación)
     */
    case DISTANCE = 'D';
}
