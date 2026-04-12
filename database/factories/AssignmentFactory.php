<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'course_id'         => Course::factory(),
            'title'             => $this->faker->sentence(4),
            'description'       => $this->faker->paragraph(),
            'deadline'          => now()->addDays($this->faker->numberBetween(1, 30)),
            'max_score'         => 100,
            'type'              => 'tugas',
            'submission_format' => 'pdf',
            'order'             => $this->faker->numberBetween(1, 10),
        ];
    }

    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
        ]);
    }

    public function tugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tugas',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => now()->subDays(1),
        ]);
    }
}
