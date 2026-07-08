<?php

namespace App\Models;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\SettingTypeEk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuraciones del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'code',
    'description',
    'value',
    'type_ek',
])]
class Setting extends Model
{
    /**
     * Transformación de los datos
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * Solicita o registra una configuración
     */
    public static function value(string $code, mixed $value = null, ?string $description = null, SettingTypeEk $type_ek = SettingTypeEk::STRING): mixed
    {
        $setting = self::where('key', $code)->first();

        if ($value !== null || $description !== null) {
            $toSave = [];

            // Si se proporciona un valor, se guarda
            if ($value !== null) {
                $toSave['value'] = $value;
            }

            // Si se proporciona una descripción, se guarda
            if ($description !== null) {
                $toSave['description'] = $description;
            }

            // Si se proporciona un valor o una descripción, se actualiza o se crea la configuración
            if ($setting) {
                return $setting->update($toSave);
            } else {
                $toSave['key'] = $code;
                $toSave['type_ek'] = $type_ek;

                return self::create($toSave);
            }
        }

        // Si no se proporciona un valor o una descripción, se retorna el valor de la configuración
        return $setting?->value;
    }
}
