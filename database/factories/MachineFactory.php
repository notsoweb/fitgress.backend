<?php

namespace Database\Factories;

use App\Emums\MachineTypeEk;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'code' => fake()->unique()->bothify('MCH-####'),
            'type_ek' => fake()->randomElement(MachineTypeEk::values()),
        ];
    }

    /**
     * Máquina de tipo peso
     */
    public function weight(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::WEIGHT->value,
        ]);
    }

    /**
     * Máquina de tipo tiempo
     */
    public function time(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::TIME->value,
        ]);
    }

    /**
     * Máquina con propiedades de ejemplo
     */
    public function withProperties(): static
    {
        return $this->afterCreating(function (Machine $machine) {
            $machine->properties()->createMany([
                ['name' => 'Altura del asiento', 'value' => (string) fake()->numberBetween(1, 10), 'unit' => 'cm', 'position' => 0],
                ['name' => 'Distancia al pecho', 'value' => (string) fake()->numberBetween(20, 60), 'unit' => 'cm', 'position' => 1],
            ]);
        });
    }
}
