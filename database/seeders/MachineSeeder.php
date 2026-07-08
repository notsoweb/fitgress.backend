<?php

namespace Database\Seeders;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Emums\MachineTypeEk;
use App\Models\Exercise;
use App\Models\Machine;
use Illuminate\Database\Seeder;

/**
 * Máquinas y ejercicios de ejemplo del gimnasio
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 2.0.0
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
                'exercises' => [
                    [
                        'name' => 'Press de banca plano',
                        'properties' => [
                            ['name' => 'Altura del asiento', 'value' => '3', 'unit' => 'nivel'],
                            ['name' => 'Inclinación', 'value' => '0', 'unit' => '°'],
                        ],
                    ],
                    ['name' => 'Press de banca inclinado', 'properties' => []],
                ],
            ],
            [
                'name' => 'Sentadilla (rack)',
                'code' => 'MCH-SENTADILLA',
                'type_ek' => MachineTypeEk::WEIGHT,
                'exercises' => [
                    [
                        'name' => 'Sentadilla trasera',
                        'properties' => [
                            ['name' => 'Altura de la barra', 'value' => '150', 'unit' => 'cm'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Caminadora',
                'code' => 'MCH-CAMINADORA',
                'type_ek' => MachineTypeEk::DISTANCE,
                'exercises' => [
                    [
                        'name' => 'Caminata',
                        'properties' => [
                            ['name' => 'Inclinación', 'value' => '5', 'unit' => '%'],
                        ],
                    ],
                    ['name' => 'Trote', 'properties' => []],
                ],
            ],
            [
                'name' => 'Bicicleta estática',
                'code' => 'MCH-BICI',
                'type_ek' => MachineTypeEk::DISTANCE,
                'exercises' => [
                    [
                        'name' => 'Ciclismo estático',
                        'properties' => [
                            ['name' => 'Altura del asiento', 'value' => '8', 'unit' => 'nivel'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($machines as $data) {
            $exercises = $data['exercises'];

            $machine = Machine::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type_ek' => $data['type_ek']->value,
                    'description' => null,
                ],
            );

            if (! $machine->wasRecentlyCreated) {
                continue;
            }

            foreach ($exercises as $exerciseData) {
                $exercise = Exercise::create([
                    'machine_id' => $machine->id,
                    'name' => $exerciseData['name'],
                    'description' => null,
                    'type_ek' => null,
                ]);

                foreach ($exerciseData['properties'] as $index => $prop) {
                    $exercise->properties()->create([
                        'name' => $prop['name'],
                        'value' => $prop['value'],
                        'unit' => $prop['unit'],
                        'position' => $index,
                    ]);
                }
            }
        }

        // Ejercicios sin máquina (peso corporal / repeticiones)
        $bodyweight = [
            'Lagartijas',
            'Abdominales',
            'Sentadilla sin peso',
        ];

        foreach ($bodyweight as $name) {
            Exercise::firstOrCreate(
                ['name' => $name],
                [
                    'machine_id' => null,
                    'description' => null,
                    'type_ek' => MachineTypeEk::REPS->value,
                ],
            );
        }
    }
}
