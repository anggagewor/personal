<?php

namespace Database\Factories;

use Modules\Calendar\Infrastructure\Models\CalendarEventModel as CalendarEvent;
use Modules\User\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+1 hour'),
            'all_day' => false,
            'color' => fake()->hexColor(),
        ];
    }

    public function allDay(): static
    {
        return $this->state(['all_day' => true, 'end_at' => null]);
    }
}
