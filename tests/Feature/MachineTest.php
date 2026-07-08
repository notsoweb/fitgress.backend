<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MachineTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['index', 'create', 'edit', 'destroy'] as $action) {
            Permission::firstOrCreate(['name' => "machines.{$action}"], ['guard_name' => 'api']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'api')->get());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_machines(): void
    {
        Machine::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'api')->getJson('/api/gym/machines');

        $response->assertOk()->assertJsonStructure(['data' => ['models' => ['data' => [['id', 'name', 'code', 'type_ek']]]]]);
    }

    public function test_admin_can_create_machine(): void
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/api/gym/machines', [
            'name' => 'Press banca',
            'description' => 'Banco plano',
            'code' => 'MCH-PRESS-BANCA',
            'type_ek' => 'W',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('gym_machines', ['code' => 'MCH-PRESS-BANCA']);
    }

    public function test_admin_can_update_machine(): void
    {
        $machine = Machine::factory()->weight()->create();

        $response = $this->actingAs($this->admin, 'api')->putJson("/api/gym/machines/{$machine->id}", [
            'name' => 'Actualizada',
            'description' => null,
            'code' => $machine->code,
            'type_ek' => 'D',
        ]);

        $response->assertOk();
        $this->assertSame('Actualizada', $machine->fresh()->name);
        $this->assertSame('D', $machine->fresh()->type_ek->value);
    }

    public function test_admin_can_destroy_machine(): void
    {
        $machine = Machine::factory()->create();

        $this->actingAs($this->admin, 'api')->deleteJson("/api/gym/machines/{$machine->id}")->assertOk();

        $this->assertDatabaseMissing('gym_machines', ['id' => $machine->id]);
    }

    public function test_common_user_cannot_create_machine(): void
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/api/gym/machines', [
            'name' => 'x',
            'code' => 'MCH-X',
            'type_ek' => 'W',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_machines(): void
    {
        $this->getJson('/api/gym/machines')->assertUnauthorized();
    }

    public function test_store_requires_valid_type_ek(): void
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/api/gym/machines', [
            'name' => 'x',
            'code' => 'MCH-X',
            'type_ek' => 'Z',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_create_time_machine(): void
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/api/gym/machines', [
            'name' => 'Banco isométrico',
            'description' => 'Equipo para aguante',
            'code' => 'MCH-ISOMETRICO',
            'type_ek' => 'T',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('gym_machines', ['code' => 'MCH-ISOMETRICO', 'type_ek' => 'T']);
    }
}
