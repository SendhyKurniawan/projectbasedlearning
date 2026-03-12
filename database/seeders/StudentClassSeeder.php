<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentClass;

class StudentClassSeeder extends Seeder
{
    public function run(): void
    {
        StudentClass::insert([
            ['study_program_id' => 1, 'semester_id' => 1, 'name' => 'TI-1A', 'created_at' => now(), 'updated_at' => now()],
            ['study_program_id' => 1, 'semester_id' => 1, 'name' => 'TI-1B', 'created_at' => now(), 'updated_at' => now()],
            ['study_program_id' => 2, 'semester_id' => 1, 'name' => 'TK-1A', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
