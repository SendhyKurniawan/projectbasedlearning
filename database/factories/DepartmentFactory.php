<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data Department (jurusan) palsu.
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Department',
            'code' => strtoupper($this->faker->bothify('??##')),
        ];
    }
}
