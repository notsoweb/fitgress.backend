<?php

namespace Database\Factories;

use App\Emums\MachineTypeEk;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registro>
 */
class RegistroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $machine = Machine::factory()->create();

        return [
            'user_id' => User::factory(),
            'machine_id' => $machine->id,
            'plan_id' => null,
            'series' => fake()->numberBetween(1, 6),
            'reps' => fake()->numberBetween(5, 20),
            'weight' => $machine->type_ek === MachineTypeEk::WEIGHT
                ? fake()->randomFloat(3, 5, 120)
                : null,
            'performed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Para una máquina y usuario específicos
     */
    public function forMachine(Machine $machine, ?User $user = null, ?Plan $plan = null): static
    {
        return $this->state(fn (array $attributes) => [
            'machine_id' => $machine->id,
            'user_id' => $user?->id ?? User::factory(),
            'plan_id' => $plan?->id,
            'weight' => $machine->type_ek === MachineTypeEk::WEIGHT
                ? fake()->randomFloat(3, 5, 120)
                : null,
        ]);
    }
}
