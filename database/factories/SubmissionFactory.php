<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'mahasiswa_id'  => User::factory()->state(['role' => 'mahasiswa']),
            'submitted_at'  => now(),
            'status'        => 'submitted',
            'notes'         => $this->faker->sentence(),
            'score'         => null,
        ];
    }

    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'score'    => $this->faker->numberBetween(60, 100),
            'feedback' => $this->faker->sentence(),
            'status'   => 'graded',
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }
}
