<?php

namespace Database\Factories;

use App\Models\StudentClass;
use Illuminate\Database\Eloquent\Factories\Factory;

// Factory pembuat data StudentClass (kelas) palsu (otomatis membuat prodi & semester terkait).
class StudentClassFactory extends Factory
{
    protected $model = StudentClass::class;

    public function definition(): array
    {
        return [
            'name'             => 'Kelas ' . $this->faker->bothify('??-##'),
            'study_program_id' => \App\Models\StudyProgram::factory(),
            'semester_id'      => \App\Models\Semester::factory(),
        ];
    }
}
