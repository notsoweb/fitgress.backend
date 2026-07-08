<?php

namespace Database\Factories;

use App\Emums\MachineTypeEk;
use App\Models\Exercise;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'machine_id' => null,
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'type_ek' => fake()->randomElement(MachineTypeEk::values()),
        ];
    }

    /**
     * Ejercicio de tipo repeticiones
     */
    public function reps(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::REPS->value,
        ]);
    }

    /**
     * Ejercicio de tipo fuerza (peso)
     */
    public function weight(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::WEIGHT->value,
        ]);
    }

    /**
     * Ejercicio de tipo distancia
     */
    public function distance(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::DISTANCE->value,
        ]);
    }

    /**
     * Ejercicio de tipo tiempo (isométrico)
     */
    public function time(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_ek' => MachineTypeEk::TIME->value,
        ]);
    }

    /**
     * Ejercicio asociado a una máquina; hereda su tipo (type_ek nulo)
     */
    public function forMachine(Machine $machine): static
    {
        return $this->state(fn (array $attributes) => [
            'machine_id' => $machine->id,
            'type_ek' => null,
        ]);
    }

    /**
     * Ejercicio sin máquina asociada
     */
    public function withoutMachine(): static
    {
        return $this->state(fn (array $attributes) => [
            'machine_id' => null,
        ]);
    }

    /**
     * Ejercicio con propiedades de ejemplo
     */
    public function withProperties(): static
    {
        return $this->afterCreating(function (Exercise $exercise) {
            $exercise->properties()->createMany([
                ['name' => 'Altura del asiento', 'value' => (string) fake()->numberBetween(1, 10), 'unit' => 'cm', 'position' => 0],
                ['name' => 'Distancia al pecho', 'value' => (string) fake()->numberBetween(20, 60), 'unit' => 'cm', 'position' => 1],
            ]);
        });
    }
}
