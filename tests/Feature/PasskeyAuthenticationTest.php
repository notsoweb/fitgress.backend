<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PasskeyAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_request_passkey_authentication_options(): void
    {
        $response = $this->getJson('/api/auth/passkeys/authentication-options');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'options',
                ],
            ]);
    }

    public function test_guest_passkey_login_requires_valid_payload(): void
    {
        $response = $this->postJson('/api/auth/passkeys/login', [
            'start_authentication_response' => '{"invalid":true}',
        ]);

        $response->assertUnprocessable();
    }

    public function test_authenticated_user_can_list_passkeys(): void
    {
        $user = User::factory()->create([
            'paternal' => 'Test',
            'maternal' => 'User',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/user/passkeys');

        $response
            ->assertOk()
            ->assertJsonPath('data.passkeys', []);
    }

    public function test_authenticated_user_can_request_passkey_register_options(): void
    {
        $user = User::factory()->create([
            'paternal' => 'Test',
            'maternal' => 'User',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/user/passkeys/register-options');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'options',
                ],
            ]);

        $options = json_decode($response->json('data.options'), true);

        $this->assertNotEmpty($options['pubKeyCredParams']);
    }

    public function test_guest_cannot_access_user_passkey_routes(): void
    {
        $this->getJson('/api/user/passkeys')->assertUnauthorized();
        $this->getJson('/api/user/passkeys/register-options')->assertUnauthorized();
    }
}
