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
     * Máquina de tipo repeticiones
     */
    public function reps(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::REPS->value,
        ]);
    }

    /**
     * Máquina de tipo fuerza (peso)
     */
    public function weight(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::WEIGHT->value,
        ]);
    }

    /**
     * Máquina de tipo distancia
     */
    public function distance(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::DISTANCE->value,
        ]);
    }

    /**
     * Máquina de tipo tiempo (isométrico)
     */
    public function time(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::TIME->value,
        ]);
    }
}
