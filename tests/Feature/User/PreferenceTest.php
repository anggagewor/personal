<?php

namespace Tests\Feature\User;

use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_preferences(): void
    {
        $user = User::factory()->create(['preferences' => ['theme' => 'dark']]);

        $response = $this->actingAs($user)->getJson('/api/preferences');

        $response->assertOk()
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.primary_color', 'indigo'); // default
    }

    public function test_user_can_update_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/preferences', [
            'theme' => 'dark',
            'primary_color' => 'rose',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.primary_color', 'rose');
    }

    public function test_update_preferences_validates_allowed_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/preferences', [
            'theme' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme']);
    }

    public function test_preferences_returns_defaults_when_empty(): void
    {
        $user = User::factory()->create(['preferences' => null]);

        $response = $this->actingAs($user)->getJson('/api/preferences');

        $response->assertOk()
            ->assertJsonPath('data.theme', 'system')
            ->assertJsonPath('data.primary_color', 'indigo')
            ->assertJsonPath('data.locale', 'id')
            ->assertJsonPath('data.sidebar_collapsed', false);
    }
}
