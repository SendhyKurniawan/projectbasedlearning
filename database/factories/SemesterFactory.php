<?php

namespace Database\Factories;

use App\Models\Semester;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data Semester palsu (otomatis membuat tahun ajaran terkait, default nonaktif).
class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => $this->faker->randomElement(['Ganjil', 'Genap']),
            'start_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'is_active' => false,
        ];
    }

    // State: jadikan semester aktif.
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
