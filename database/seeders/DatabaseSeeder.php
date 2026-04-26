<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            DepartmentSeeder::class,
            StudyProgramSeeder::class,
            StudentClassSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            AssignmentSeeder::class,
            DiscussionSeeder::class,
        ]);
    }
}
