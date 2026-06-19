<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data Course palsu (otomatis membuat dosen & semester terkait).
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'nama_matkul' => $this->faker->words(3, true),
            'kode_matkul' => strtoupper($this->faker->bothify('??###')),
            'description' => $this->faker->sentence(),
            'dosen_id' => User::factory()->state(['role' => 'dosen']),
            'sks' => $this->faker->randomElement([2, 3, 4]),
            'semester_id' => Semester::factory(),
        ];
    }
}
