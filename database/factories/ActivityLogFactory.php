<?php

namespace Database\Factories;

use Modules\Activity\Infrastructure\Models\ActivityLogModel as ActivityLog;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'description' => fake()->sentence(),
            'metadata' => ['entity' => 'note', 'entity_id' => fake()->randomNumber()],
        ];
    }
}
