<?php

namespace Database\Seeders;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Models\Machine;
use App\Models\MachineProperty;
use Illuminate\Database\Seeder;

/**
 * Máquinas de ejemplo del gimnasio
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            [
                'name' => 'Press banca',
                'code' => 'MCH-PRESS-BANCA',
                'type_ek' => MachineTypeEk::WEIGHT,
                'properties' => [
                    ['name' => 'Altura del asiento', 'value' => '3', 'unit' => 'nivel'],
                    ['name' => 'Inclinación', 'value' => '0', 'unit' => '°'],
                ],
            ],
            [
                'name' => 'Sentadilla',
                'code' => 'MCH-SENTADILLA',
                'type_ek' => MachineTypeEk::WEIGHT,
                'properties' => [
                    ['name' => 'Altura de la barra', 'value' => '150', 'unit' => 'cm'],
                    ['name' => 'Distancia al pecho', 'value' => '40', 'unit' => 'cm'],
                ],
            ],
            [
                'name' => 'Peso muerto',
                'code' => 'MCH-PESO-MUERTO',
                'type_ek' => MachineTypeEk::WEIGHT,
                'properties' => [],
            ],
            [
                'name' => 'Press militar',
                'code' => 'MCH-PRESS-MILITAR',
                'type_ek' => MachineTypeEk::WEIGHT,
                'properties' => [
                    ['name' => 'Altura del asiento', 'value' => '5', 'unit' => 'nivel'],
                    ['name' => 'Distancia al pecho', 'value' => '30', 'unit' => 'cm'],
                ],
            ],
            [
                'name' => 'Caminadora',
                'code' => 'MCH-CAMINADORA',
                'type_ek' => MachineTypeEk::TIME,
                'properties' => [
                    ['name' => 'Inclinación', 'value' => '5', 'unit' => '%'],
                ],
            ],
            [
                'name' => 'Bicicleta estática',
                'code' => 'MCH-BICI',
                'type_ek' => MachineTypeEk::TIME,
                'properties' => [
                    ['name' => 'Altura del asiento', 'value' => '8', 'unit' => 'nivel'],
                    ['name' => 'Distancia al manubrio', 'value' => '45', 'unit' => 'cm'],
                ],
            ],
            [
                'name' => 'Elíptica',
                'code' => 'MCH-ELIPTICA',
                'type_ek' => MachineTypeEk::TIME,
                'properties' => [
                    ['name' => 'Nivel de resistencia', 'value' => '6', 'unit' => 'nivel'],
                ],
            ],
        ];

        foreach ($machines as $data) {
            $properties = $data['properties'];
            unset($data['properties']);

            $machine = Machine::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type_ek' => $data['type_ek']->value,
                    'description' => null,
                ],
            );

            if ($machine->wasRecentlyCreated && ! empty($properties)) {
                foreach ($properties as $index => $prop) {
                    MachineProperty::create([
                        'machine_id' => $machine->id,
                        'name' => $prop['name'],
                        'value' => $prop['value'],
                        'unit' => $prop['unit'],
                        'position' => $index,
                    ]);
                }
            }
        }
    }
}
