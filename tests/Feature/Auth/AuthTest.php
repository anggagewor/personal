<?php

namespace Tests\Feature\Auth;

use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'P@ssw0rd!23',
            'password_confirmation' => 'P@ssw0rd!23',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token', 'refresh_token']]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Another',
            'email' => 'taken@example.com',
            'password' => 'P@ssw0rd!23',
            'password_confirmation' => 'P@ssw0rd!23',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('P@ssw0rd!23')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'P@ssw0rd!23',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['user', 'token', 'refresh_token']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('P@ssw0rd!23')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_revokes_existing_tokens(): void
    {
        $user = User::factory()->create(['password' => bcrypt('P@ssw0rd!23')]);
        $user->createToken('old-token');

        $this->assertCount(1, $user->tokens);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'P@ssw0rd!23',
        ]);

        // Old token revoked, 2 new tokens (auth + refresh)
        $this->assertCount(2, $user->fresh()->tokens);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Berhasil logout.']);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_user_can_get_profile_via_me(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/auth/refresh');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token', 'refresh_token']]);
    }
}
