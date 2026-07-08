<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['machines', 'exercises', 'plans'] as $resource) {
            foreach (['index', 'create', 'edit', 'destroy'] as $action) {
                Permission::firstOrCreate(['name' => "{$resource}.{$action}"], ['guard_name' => 'api']);
            }
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'api')->get());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_exercises_with_machine_and_effective_type(): void
    {
        $machine = Machine::factory()->weight()->create();
        Exercise::factory()->forMachine($machine)->create();

        $response = $this->actingAs($this->admin, 'api')->getJson('/api/gym/exercises');

        $response->assertOk()->assertJsonStructure([
            'data' => ['models' => ['data' => [['id', 'name', 'type_ek', 'effective_type', 'machine']]]],
        ]);

        $this->assertSame('W', $response->json('data.models.data.0.effective_type'));
    }

    public function test_admin_can_create_exercise_with_properties(): void
    {
        $machine = Machine::factory()->distance()->create();

        $response = $this->actingAs($this->admin, 'api')->postJson('/api/gym/exercises', [
            'name' => 'Caminata',
            'description' => 'Cardio',
            'machine_id' => $machine->id,
            'type_ek' => null,
            'properties' => [
                ['name' => 'Inclinación', 'value' => '5', 'unit' => '%'],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('gym_exercises', ['name' => 'Caminata', 'machine_id' => $machine->id]);
        $this->assertDatabaseHas('gym_exercise_properties', ['name' => 'Inclinación', 'value' => '5']);
    }

    public function test_effective_type_falls_back_to_machine_type(): void
    {
        $machine = Machine::factory()->weight()->create();
        $exercise = Exercise::factory()->forMachine($machine)->create(['type_ek' => null]);

        $this->assertSame('W', $exercise->effective_type);
    }

    public function test_exercise_type_overrides_machine_type(): void
    {
        $machine = Machine::factory()->weight()->create();
        $exercise = Exercise::factory()->forMachine($machine)->create(['type_ek' => 'R']);

        $this->assertSame('R', $exercise->effective_type);
    }

    public function test_admin_can_update_exercise(): void
    {
        $exercise = Exercise::factory()->reps()->withoutMachine()->create();
        $exercise->properties()->create(['name' => 'Vieja', 'value' => '1', 'unit' => null, 'position' => 0]);

        $response = $this->actingAs($this->admin, 'api')->putJson("/api/gym/exercises/{$exercise->id}", [
            'name' => 'Actualizado',
            'description' => null,
            'machine_id' => null,
            'type_ek' => 'W',
            'properties' => [
                ['name' => 'Nueva', 'value' => '2', 'unit' => 'cm'],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('Actualizado', $exercise->fresh()->name);
        $this->assertSame('W', $exercise->fresh()->type_ek->value);
        $this->assertDatabaseMissing('gym_exercise_properties', ['name' => 'Vieja']);
        $this->assertDatabaseHas('gym_exercise_properties', ['name' => 'Nueva']);
    }

    public function test_show_includes_machine_and_properties(): void
    {
        $machine = Machine::factory()->weight()->create();
        $exercise = Exercise::factory()->forMachine($machine)->withProperties()->create();

        $response = $this->actingAs($this->admin, 'api')->getJson("/api/gym/exercises/{$exercise->id}");

        $response->assertOk()->assertJsonStructure([
            'data' => ['model' => ['id', 'name', 'effective_type', 'machine' => ['id', 'code'], 'properties' => [['id', 'name']]]],
        ]);
    }

    public function test_admin_can_destroy_exercise(): void
    {
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($this->admin, 'api')->deleteJson("/api/gym/exercises/{$exercise->id}")->assertOk();

        $this->assertDatabaseMissing('gym_exercises', ['id' => $exercise->id]);
    }

    public function test_common_user_cannot_create_exercise(): void
    {
        $this->actingAs($this->user, 'api')->postJson('/api/gym/exercises', [
            'name' => 'x',
        ])->assertStatus(422);
    }

    public function test_guest_cannot_access_exercises(): void
    {
        $this->getJson('/api/gym/exercises')->assertUnauthorized();
    }

    public function test_store_rejects_invalid_type_ek(): void
    {
        $this->actingAs($this->admin, 'api')->postJson('/api/gym/exercises', [
            'name' => 'x',
            'type_ek' => 'Z',
        ])->assertStatus(422);
    }
}
