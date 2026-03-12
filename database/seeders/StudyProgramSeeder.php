<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudyProgram;

class StudyProgramSeeder extends Seeder
{
    public function run(): void
    {
        StudyProgram::insert([
            ['department_id' => 1, 'name' => 'D4 Teknik Informatika', 'code' => 'TI', 'level' => 'D4', 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => 1, 'name' => 'D3 Teknik Komputer', 'code' => 'TK', 'level' => 'D3', 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => 2, 'name' => 'D3 Teknik Listrik', 'code' => 'TL', 'level' => 'D3', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
