<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExerciseResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_fetch_exercise_catalog(): void
    {
        $this->postJson('/api/catalogs/get', ['exercise:all' => []])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_receives_every_exercise_via_catalog(): void
    {
        Exercise::factory()->count(16)->reps()->create();

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/catalogs/get', ['exercise:all' => []]);

        $response->assertOk()
            ->assertJsonCount(16, 'data.exercise:all')
            ->assertJsonStructure([
                'data' => [
                    'exercise:all' => [
                        ['id', 'name', 'type_ek', 'effective_type', 'machine', 'properties'],
                    ],
                ],
            ]);

        $this->assertFalse(array_key_exists('current_page', $response->json('data.exercise:all.0') ?? []));
    }

    public function test_resources_get_alias_returns_the_same_catalog(): void
    {
        Exercise::factory()->reps()->create(['name' => 'Press banca']);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/resources/get', ['exercise:all' => []])
            ->assertOk()
            ->assertJsonPath('data.exercise:all.0.name', 'Press banca');
    }
}
