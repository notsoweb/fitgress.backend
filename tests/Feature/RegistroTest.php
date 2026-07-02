<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_registro_for_weight_machine(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'machine_id' => $machine->id,
            'plan_id' => null,
            'series' => 4,
            'reps' => 10,
            'weight' => 80.5,
            'performed_at' => '2026-07-02 10:00:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('gym_registros', [
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'series' => 4,
            'reps' => 10,
            'weight' => 80.5,
        ]);
    }

    public function test_weight_machine_requires_weight_value(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'machine_id' => $machine->id,
            'series' => 4,
            'reps' => 10,
            'performed_at' => '2026-07-02 10:00:00',
        ]);

        $response->assertStatus(422);
    }

    public function test_time_machine_rejects_weight(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->time()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'machine_id' => $machine->id,
            'series' => 1,
            'reps' => 30,
            'weight' => 20,
            'performed_at' => '2026-07-02 10:00:00',
        ]);

        $response->assertStatus(422);
    }

    public function test_time_machine_accepts_registro_without_weight(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->time()->create();

        $this->actingAs($user, 'api')->postJson('/api/gym/registros', [
            'machine_id' => $machine->id,
            'series' => 1,
            'reps' => 30,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertStatus(201);

        $this->assertDatabaseHas('gym_registros', [
            'machine_id' => $machine->id,
            'weight' => null,
        ]);
    }

    public function test_user_only_sees_own_registros(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $machine = Machine::factory()->weight()->create();

        Registro::factory()->forMachine($machine, $other)->create();
        Registro::factory()->forMachine($machine, $user)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.models.data'));
        $this->assertSame($user->id, (int) $response->json('data.models.data.0.user_id'));
    }

    public function test_user_cannot_show_other_users_registro(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $machine = Machine::factory()->weight()->create();
        $registro = Registro::factory()->forMachine($machine, $other)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/gym/registros/{$registro->id}")
            ->assertForbidden();
    }

    public function test_owner_can_update_and_destroy_own_registro(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();
        $registro = Registro::factory()->forMachine($machine, $user)->create();

        $this->actingAs($user, 'api')->putJson("/api/gym/registros/{$registro->id}", [
            'machine_id' => $machine->id,
            'series' => 5,
            'reps' => 8,
            'weight' => 90,
            'performed_at' => '2026-07-02 11:00:00',
        ])->assertOk();

        $this->assertSame(5, $registro->fresh()->series);

        $this->actingAs($user, 'api')->deleteJson("/api/gym/registros/{$registro->id}")->assertOk();
        $this->assertDatabaseMissing('gym_registros', ['id' => $registro->id]);
    }

    public function test_charts_returns_time_series(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();

        Registro::factory()->forMachine($machine, $user)->create([
            'weight' => 60,
            'performed_at' => '2026-06-01 10:00:00',
        ]);
        Registro::factory()->forMachine($machine, $user)->create([
            'weight' => 70,
            'performed_at' => '2026-06-15 10:00:00',
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?machine_id='.$machine->id.'&metric=weight');

        $response->assertOk()->assertJsonStructure([
            'data' => ['machine', 'metrics', 'series' => [['metric', 'data' => [['performed_at', 'value']]]]],
        ]);

        $this->assertCount(1, $response->json('data.series'));
        $this->assertSame(['weight'], $response->json('data.metrics'));
    }

    public function test_charts_without_metric_returns_all_metrics(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->weight()->create();

        Registro::factory()->forMachine($machine, $user)->create([
            'series' => 3,
            'reps' => 10,
            'weight' => 60,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?machine_id='.$machine->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['series', 'reps', 'weight']);
        $this->assertCount(3, $response->json('data.series'));
    }

    public function test_charts_on_time_machine_omits_weight_when_no_metric(): void
    {
        $user = User::factory()->create();
        $machine = Machine::factory()->time()->create();

        Registro::factory()->forMachine($machine, $user)->create([
            'series' => 1,
            'reps' => 30,
            'weight' => null,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/gym/registros/charts?machine_id='.$machine->id);

        $response->assertOk()->assertJsonPath('data.metrics', ['series', 'reps']);
        $this->assertCount(2, $response->json('data.series'));
    }

    public function test_guest_cannot_create_registro(): void
    {
        $machine = Machine::factory()->weight()->create();

        $this->postJson('/api/gym/registros', [
            'machine_id' => $machine->id,
            'series' => 1,
            'reps' => 1,
            'weight' => 1,
            'performed_at' => '2026-07-02 10:00:00',
        ])->assertUnauthorized();
    }
}
