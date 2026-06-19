<?php

namespace Database\Factories;

use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data StudyProgram (prodi) palsu (otomatis membuat jurusan terkait).
class StudyProgramFactory extends Factory
{
    protected $model = StudyProgram::class;

    public function definition(): array
    {
        return [
            'department_id' => \App\Models\Department::factory(),
            'name'          => $this->faker->words(3, true) . ' Studies',
            'code'          => strtoupper($this->faker->bothify('??###')),
            'level'         => $this->faker->randomElement(['D3', 'S1', 'S2']),
        ];
    }
}
