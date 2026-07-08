<?php

namespace Database\Factories;

use App\Emums\MachineTypeEk;
use App\Models\Exercise;
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
        $exercise = Exercise::factory()->create();

        return array_merge([
            'user_id' => User::factory(),
            'exercise_id' => $exercise->id,
            'plan_id' => null,
            'performed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ], $this->metricsFor($exercise->effectiveType()));
    }

    /**
     * Para un ejercicio y usuario específicos
     */
    public function forExercise(Exercise $exercise, ?User $user = null, ?Plan $plan = null): static
    {
        return $this->state(fn (array $attributes) => array_merge([
            'exercise_id' => $exercise->id,
            'user_id' => $user?->id ?? User::factory(),
            'plan_id' => $plan?->id,
        ], $this->metricsFor($exercise->effectiveType())));
    }

    /**
     * Estado de registro de repeticiones
     */
    public function reps(): static
    {
        return $this->state(fn (array $attributes) => $this->metricsFor(MachineTypeEk::REPS));
    }

    /**
     * Estado de registro de fuerza (peso)
     */
    public function weight(): static
    {
        return $this->state(fn (array $attributes) => $this->metricsFor(MachineTypeEk::WEIGHT));
    }

    /**
     * Estado de registro de distancia
     */
    public function distance(): static
    {
        return $this->state(fn (array $attributes) => $this->metricsFor(MachineTypeEk::DISTANCE));
    }

    /**
     * Generar métricas coherentes con el tipo efectivo indicado
     *
     * @return array<string, mixed>
     */
    private function metricsFor(?MachineTypeEk $type): array
    {
        $empty = [
            'series' => null,
            'reps' => null,
            'weight' => null,
            'duration' => null,
            'distance' => null,
            'speed' => null,
            'incline' => null,
        ];

        return match ($type) {
            MachineTypeEk::WEIGHT => array_merge($empty, [
                'series' => fake()->numberBetween(1, 6),
                'reps' => fake()->numberBetween(5, 20),
                'weight' => fake()->randomFloat(3, 5, 120),
            ]),
            MachineTypeEk::DISTANCE => array_merge($empty, [
                'duration' => fake()->numberBetween(5, 90),
                'distance' => fake()->randomFloat(3, 0.5, 20),
                'speed' => fake()->randomFloat(2, 3, 18),
                'incline' => fake()->randomFloat(2, 0, 15),
            ]),
            default => array_merge($empty, [
                'series' => fake()->numberBetween(1, 6),
                'reps' => fake()->numberBetween(5, 20),
            ]),
        };
    }
}
