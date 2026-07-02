<?php

namespace Tests\Feature;

use App\Models\Machine;
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

    public function test_machines_endpoint_returns_ordered_machines(): void
    {
        $plan = Plan::factory()->create();
        $m1 = Machine::factory()->create();
        $m2 = Machine::factory()->create();

        $plan->machines()->sync([$m1->id => ['position' => 1], $m2->id => ['position' => 0]]);

        $response = $this->actingAs($this->admin, 'api')->getJson("/api/gym/plans/{$plan->id}/machines");

        $response->assertOk()->assertJsonPath('data.machines.0.id', $m2->id);
    }

    public function test_sync_requires_machines_array(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'api')
            ->putJson("/api/gym/plans/{$plan->id}/machines", ['machines' => []])
            ->assertStatus(422);
    }

    public function test_common_user_cannot_create_plan(): void
    {
        $this->actingAs(User::factory()->create(), 'api')
            ->postJson('/api/gym/plans', ['name' => 'x'])
            ->assertStatus(422);
    }

    public function test_admin_can_attach_machines_to_plan(): void
    {
        $plan = Plan::factory()->create();
        $m1 = Machine::factory()->create();
        $m2 = Machine::factory()->create();

        $response = $this->actingAs($this->admin, 'api')->putJson("/api/gym/plans/{$plan->id}/machines", [
            'machines' => [
                ['id' => $m1->id, 'position' => 0],
                ['id' => $m2->id, 'position' => 1],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.machines.0.id', $m1->id);
        $this->assertSame([$m1->id, $m2->id], $plan->machines()->pluck('gym_machines.id')->toArray());
    }
}
