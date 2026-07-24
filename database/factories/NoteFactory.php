<?php

namespace Database\Factories;

use Modules\Note\Infrastructure\Models\NoteModel as Note;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraphs(2, true),
            'is_pinned' => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(['is_pinned' => true]);
    }
}
