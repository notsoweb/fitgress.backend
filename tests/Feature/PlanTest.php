<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['index', 'create', 'edit', 'destroy'] as $action) {
            Permission::firstOrCreate(['name' => "plans.{$action}"], ['guard_name' => 'api']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'api')->get());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
    }

    public function test_admin_can_list_plans(): void
    {
        Plan::factory()->count(2)->create();

        $this->actingAs($this->admin, 'api')
            ->getJson('/api/gym/plans')
            ->assertOk()
            ->assertJsonStructure(['data' => ['models' => ['data' => [['id', 'name']]]]]);
    }

    public function test_admin_can_create_plan(): void
    {
        $this->actingAs($this->admin, 'api')
            ->postJson('/api/gym/plans', ['name' => 'Full body', 'description' => 'Rutina'])
            ->assertStatus(201);

        $this->assertDatabaseHas('gym_plans', ['name' => 'Full body']);
    }

    public function test_admin_can_update_plan(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'api')
            ->putJson("/api/gym/plans/{$plan->id}", ['name' => 'Updated', 'description' => null])
            ->assertOk();

        $this->assertSame('Updated', $plan->fresh()->name);
    }

    public function test_admin_can_destroy_plan(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/gym/plans/{$plan->id}")
            ->assertOk();

        $this->assertDatabaseMissing('gym_plans', ['id' => $plan->id]);
    }

    public function test_exercises_endpoint_returns_ordered_exercises(): void
    {
        $plan = Plan::factory()->create();
        $e1 = Exercise::factory()->reps()->create();
        $e2 = Exercise::factory()->reps()->create();

        $plan->exercises()->sync([$e1->id => ['position' => 1], $e2->id => ['position' => 0]]);

        $response = $this->actingAs($this->admin, 'api')->getJson("/api/gym/plans/{$plan->id}/exercises");

        $response->assertOk()->assertJsonPath('data.exercises.0.id', $e2->id);
    }

    public function test_sync_requires_exercises_array(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'api')
            ->putJson("/api/gym/plans/{$plan->id}/exercises", ['exercises' => []])
            ->assertStatus(422);
    }

    public function test_common_user_cannot_create_plan(): void
    {
        $this->actingAs(User::factory()->create(), 'api')
            ->postJson('/api/gym/plans', ['name' => 'x'])
            ->assertStatus(422);
    }

    public function test_admin_can_attach_exercises_to_plan(): void
    {
        $plan = Plan::factory()->create();
        $e1 = Exercise::factory()->reps()->create();
        $e2 = Exercise::factory()->weight()->create();

        $response = $this->actingAs($this->admin, 'api')->putJson("/api/gym/plans/{$plan->id}/exercises", [
            'exercises' => [
                ['id' => $e1->id, 'position' => 0],
                ['id' => $e2->id, 'position' => 1],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.exercises.0.id', $e1->id);
        $this->assertSame([$e1->id, $e2->id], $plan->exercises()->pluck('gym_exercises.id')->toArray());
    }

    public function test_show_includes_exercises_with_machine(): void
    {
        $plan = Plan::factory()->create();
        $exercise = Exercise::factory()->weight()->create();
        $plan->exercises()->sync([$exercise->id => ['position' => 0]]);

        $this->actingAs($this->admin, 'api')
            ->getJson("/api/gym/plans/{$plan->id}")
            ->assertOk()
            ->assertJsonPath('data.model.exercises.0.id', $exercise->id)
            ->assertJsonPath('data.model.exercises.0.effective_type', 'W');
    }
}
