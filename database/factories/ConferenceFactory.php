<?php

namespace Database\Factories;

use App\Models\Conference;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConferenceFactory extends Factory
{
    protected $model = Conference::class;

    public function definition(): array
    {
        return [
            'course_id'    => Course::factory(),
            'dosen_id'     => User::factory()->state(['role' => 'dosen']),
            'title'        => $this->faker->sentence(4),
            'description'  => $this->faker->paragraph(),
            'room_name'    => 'room-' . Str::uuid(),
            'scheduled_at' => now()->addDays($this->faker->numberBetween(1, 14)),
            'status'       => 'scheduled',
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'live',
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'ended',
            'ended_at' => now()->subHour(),
        ]);
    }
}
