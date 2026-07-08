<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseNote;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_create_reps_registro(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'reps' => 12,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(201);

        $this->assertDatabaseHas('gym_registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'reps' => 12,
            'weight' => null,
            'duration' => null,
        ]);
    }

    public function test_reps_registro_rejects_weight(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'reps' => 12,
            'weight' => 10,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_user_can_create_weight_registro(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 4,
            'reps' => 10,
            'weight' => 80.5,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(201);

        $this->assertDatabaseHas('gym_registros', [
            'exercise_id' => $exercise->id,
            'series' => 4,
            'weight' => 80.5,
        ]);
    }

    public function test_weight_registro_requires_weight(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 4,
            'reps' => 10,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_user_can_create_distance_registro(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->distance()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'duration' => 30,
            'distance' => 5.2,
            'speed' => 10.4,
            'incline' => 3.5,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(201);

        $this->assertDatabaseHas('gym_registros', [
            'exercise_id' => $exercise->id,
            'duration' => 30,
        ]);
    }

    public function test_distance_registro_requires_duration_and_distance(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->distance()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'speed' => 10,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_distance_registro_rejects_series(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->distance()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'duration' => 30,
            'distance' => 5,
            'series' => 3,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_user_can_create_time_registro(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $exercise = Exercise::factory()->time()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'duration' => 50,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(201);

        $this->assertDatabaseHas('gym_registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'duration' => 50,
            'reps' => null,
            'weight' => null,
            'distance' => null,
        ]);
    }

    public function test_time_registro_rejects_other_metrics(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $exercise = Exercise::factory()->time()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 3,
            'duration' => 50,
            'reps' => 12,
            'weight' => 10,
            'distance' => 1.2,
            'speed' => 8,
            'incline' => 5,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_effective_type_from_machine_is_used_for_validation(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();
        $exercise = Exercise::factory()->forMachine($machine)->create(['type_ek' => null]);

        // Sin weight → debe fallar porque hereda tipo W de la máquina
        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 4,
            'reps' => 10,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(422);
    }

    public function test_user_only_sees_own_registros(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        Registro::factory()->forExercise($exercise, $other)->create();
        Registro::factory()->forExercise($exercise, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.models.data'));
        $this->assertSame($user->id, (int) $response->json('data.models.data.0.user_id'));
    }

    public function test_user_cannot_show_other_users_registro(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();
        $registro = Registro::factory()->forExercise($exercise, $other)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/gym/registros/{$registro->id}")
            ->assertForbidden();
    }

    public function test_owner_can_update_and_destroy_own_registro(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();
        $registro = Registro::factory()->forExercise($exercise, $user)->create();

        $this->actingAs($user, 'api')->putJson("/api/gym/registros/{$registro->id}", [
            'exercise_id' => $exercise->id,
            'series' => 5,
            'reps' => 8,
            'weight' => 90,
            'performed_at' => '2026-07-02 11:00:00',
        ])->assertOk();

        $this->assertSame(5, $registro->fresh()->series);

        $this->actingAs($user, 'api')->deleteJson("/api/gym/registros/{$registro->id}")->assertOk();
        $this->assertDatabaseMissing('gym_registros', ['id' => $registro->id]);
    }

    public function test_last_returns_latest_registro_and_note(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        Registro::factory()->forExercise($exercise, $user)->create([
            'performed_at' => '2026-06-01 10:00:00',
        ]);
        $latest = Registro::factory()->forExercise($exercise, $user)->create([
            'performed_at' => '2026-06-20 10:00:00',
        ]);

        ExerciseNote::create(['user_id' => $user->id, 'exercise_id' => $exercise->id, 'note' => 'Recordatorio']);

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/last?exercise_id='.$exercise->id);

        $response->assertOk()
            ->assertJsonPath('data.model.id', $latest->id)
            ->assertJsonPath('data.note', 'Recordatorio');
    }

    public function test_last_returns_null_when_no_registro(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($user, 'api')->getJson('/api/gym/registros/last?exercise_id='.$exercise->id)
            ->assertOk()
            ->assertJsonPath('data.model', null)
            ->assertJsonPath('data.note', null);
    }

    public function test_session_returns_only_completed_exercises_for_selected_date_and_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $other */
        $other = User::factory()->create();
        /** @var Plan $plan */
        $plan = Plan::factory()->create();
        $completedExercise = Exercise::factory()->weight()->create();
        $otherDayExercise = Exercise::factory()->weight()->create();
        $otherUserExercise = Exercise::factory()->weight()->create();

        $plan->exercises()->attach([
            $completedExercise->id => ['position' => 0],
            $otherDayExercise->id => ['position' => 1],
            $otherUserExercise->id => ['position' => 2],
        ]);

        Registro::factory()->forExercise($completedExercise, $user, $plan)->create([
            'performed_at' => '2026-07-08 06:30:00',
        ]);
        Registro::factory()->forExercise($otherDayExercise, $user, $plan)->create([
            'performed_at' => '2026-07-07 23:59:59',
        ]);
        Registro::factory()->forExercise($otherUserExercise, $other, $plan)->create([
            'performed_at' => '2026-07-08 08:00:00',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/gym/registros/session?plan_id={$plan->id}&date=2026-07-08");

        $response->assertOk()
            ->assertJsonPath('data.completed_exercise_ids', [$completedExercise->id]);
    }

    public function test_charts_returns_time_series_for_metric(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        Registro::factory()->forExercise($exercise, $user)->create([
            'weight' => 60,
            'performed_at' => '2026-06-01 10:00:00',
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?exercise_id='.$exercise->id.'&metric=weight');

        $response->assertOk()->assertJsonStructure([
            'data' => ['exercise', 'metrics', 'series' => [['metric', 'data' => [['performed_at', 'value']]]]],
        ]);
        $this->assertSame(['weight'], $response->json('data.metrics'));
    }

    public function test_charts_default_metrics_for_weight_type(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->weight()->create();

        Registro::factory()->forExercise($exercise, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?exercise_id='.$exercise->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['series', 'reps', 'weight']);
    }

    public function test_charts_default_metrics_for_distance_type(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->distance()->create();

        Registro::factory()->forExercise($exercise, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?exercise_id='.$exercise->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['duration', 'distance', 'speed', 'incline']);
    }

    public function test_charts_default_metrics_for_time_type(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $exercise = Exercise::factory()->time()->create();

        Registro::factory()->forExercise($exercise, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?exercise_id='.$exercise->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['series', 'duration']);
    }

    public function test_charts_default_metrics_for_reps_type(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        Registro::factory()->forExercise($exercise, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?exercise_id='.$exercise->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['series', 'reps']);
    }

    public function test_guest_cannot_create_registro(): void
    {
        $exercise = Exercise::factory()->weight()->create();

        $this->postJson('/api/gym/registros', [
            'exercise_id' => $exercise->id,
            'series' => 1,
            'reps' => 1,
            'weight' => 1,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertUnauthorized();
    }
}
