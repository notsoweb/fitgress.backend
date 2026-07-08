<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseNote;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExerciseNoteTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_note_is_null_when_absent(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/gym/exercises/{$exercise->id}/note")
            ->assertOk()
            ->assertJsonPath('data.note', null);
    }

    public function test_user_can_upsert_note(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        $this->actingAs($user, 'api')
            ->putJson("/api/gym/exercises/{$exercise->id}/note", ['note' => 'Subir peso gradualmente'])
            ->assertOk()
            ->assertJsonPath('data.note', 'Subir peso gradualmente');

        $this->assertDatabaseHas('gym_exercise_notes', [
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'note' => 'Subir peso gradualmente',
        ]);

        $this->actingAs($user, 'api')
            ->putJson("/api/gym/exercises/{$exercise->id}/note", ['note' => 'Actualizada'])
            ->assertOk()
            ->assertJsonPath('data.note', 'Actualizada');

        $this->assertSame(1, ExerciseNote::where('user_id', $user->id)->where('exercise_id', $exercise->id)->count());
    }

    public function test_blank_note_removes_it(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        ExerciseNote::create(['user_id' => $user->id, 'exercise_id' => $exercise->id, 'note' => 'algo']);

        $this->actingAs($user, 'api')
            ->putJson("/api/gym/exercises/{$exercise->id}/note", ['note' => null])
            ->assertOk()
            ->assertJsonPath('data.note', null);

        $this->assertDatabaseMissing('gym_exercise_notes', [
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
        ]);
    }

    public function test_note_is_scoped_to_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $exercise = Exercise::factory()->reps()->create();

        ExerciseNote::create(['user_id' => $other->id, 'exercise_id' => $exercise->id, 'note' => 'de otro']);

        $this->actingAs($user, 'api')
            ->getJson("/api/gym/exercises/{$exercise->id}/note")
            ->assertOk()
            ->assertJsonPath('data.note', null);
    }

    public function test_guest_cannot_access_note(): void
    {
        $exercise = Exercise::factory()->reps()->create();

        $this->getJson("/api/gym/exercises/{$exercise->id}/note")->assertUnauthorized();
    }
}
