<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data AcademicYear palsu (default nonaktif).
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = $this->faker->unique()->numberBetween(2020, 2030);
        return [
            'year_start' => $startYear,
            'year_end' => $startYear + 1,
            'is_active' => false,
        ];
    }

    // State: jadikan tahun ajaran aktif.
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
