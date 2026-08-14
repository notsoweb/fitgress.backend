<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_view_dashboard_summary(): void
    {
        $this->getJson('/api/gym/dashboard')->assertUnauthorized();
    }

    public function test_authenticated_user_receives_empty_summary(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/gym/dashboard')
            ->assertOk()
            ->assertJsonPath('data.machines_count', 0)
            ->assertJsonPath('data.exercises_count', 0)
            ->assertJsonPath('data.workout_days', 0)
            ->assertJsonPath('data.top_exercise', null)
            ->assertJsonPath('data.calendar', []);
    }

    public function test_summary_counts_catalog_and_user_workouts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Machine::factory()->count(2)->reps()->create();
        $squat = Exercise::factory()->reps()->create(['name' => 'Sentadilla']);
        $bench = Exercise::factory()->reps()->create(['name' => 'Press banca']);
        Exercise::factory()->reps()->create(['name' => 'Remo']);
        $plan = Plan::factory()->create(['name' => 'Full body']);

        Registro::factory()->forExercise($squat, $user, $plan)->create(['performed_at' => '2026-07-01 10:00:00']);
        Registro::factory()->forExercise($bench, $user, $plan)->create(['performed_at' => '2026-07-01 11:00:00']);
        Registro::factory()->forExercise($squat, $user)->create(['performed_at' => '2026-07-02 09:00:00']);
        Registro::factory()->forExercise($bench, $other, $plan)->count(10)->create(['performed_at' => '2026-07-03 08:00:00']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/gym/dashboard')
            ->assertOk()
            ->assertJsonPath('data.machines_count', Machine::query()->count())
            ->assertJsonPath('data.exercises_count', Exercise::query()->count())
            ->assertJsonPath('data.workout_days', 2)
            ->assertJsonPath('data.top_exercise.id', $squat->id)
            ->assertJsonPath('data.top_exercise.name', 'Sentadilla')
            ->assertJsonPath('data.top_exercise.times', 2)
            ->assertJsonCount(2, 'data.calendar');

        $this->assertSame('2026-07-01', $response->json('data.calendar.0.date'));
        $this->assertSame($plan->id, $response->json('data.calendar.0.plans.0.id'));
        $this->assertSame('Full body', $response->json('data.calendar.0.plans.0.name'));
        $this->assertSame('2026-07-02', $response->json('data.calendar.1.date'));
        $this->assertNull($response->json('data.calendar.1.plans.0.id'));
    }
}
