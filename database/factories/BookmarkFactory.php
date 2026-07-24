<?php

namespace Database\Factories;

use Modules\Bookmark\Infrastructure\Models\BookmarkModel as Bookmark;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'url' => fake()->url(),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Work', 'Personal', 'Dev', 'Design']),
            'icon' => null,
        ];
    }
}
