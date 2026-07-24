<?php

namespace Tests\Feature\User;

use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'avatar', 'created_at']]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldP@ss1!')]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'OldP@ss1!',
            'password' => 'N3wP@ssw0rd!',
            'password_confirmation' => 'N3wP@ssw0rd!',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password berhasil diubah.']);
    }

    public function test_update_password_fails_with_wrong_current(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldP@ss1!')]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'WrongPassword1!',
            'password' => 'N3wP@ssw0rd!',
            'password_confirmation' => 'N3wP@ssw0rd!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['avatar']]);

        $this->assertNotNull($user->fresh()->avatar);
    }

    public function test_avatar_upload_rejects_invalid_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->create('document.pdf', 1024),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_rejects_too_large_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_user_can_remove_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'avatars/test.jpg']);

        $response = $this->actingAs($user)->deleteJson('/api/profile/avatar');

        $response->assertOk();
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }
}
