<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data Assignment palsu (default tipe 'tugas' format pdf).
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

    // State: jadikan tugas bertipe quiz.
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
        ]);
    }

    // State: jadikan tugas bertipe tugas.
    public function tugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tugas',
        ]);
    }

    // State: jadikan tugas sudah lewat deadline (untuk uji skenario terlambat).
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => now()->subDays(1),
        ]);
    }
}
