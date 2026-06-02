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
            // Real Politeknik Negeri Media Kreatif (PoliMedia) Jakarta testing ground,
            // over a 4-year calendar (8 semesters). Regenerate structure with:
            //   python scripts/pddikti/fetch_polimedia.py
            \Database\Seeders\Polimedia\CalendarSeeder::class,
            \Database\Seeders\Polimedia\StructureSeeder::class,
            \Database\Seeders\Polimedia\UsersSeeder::class,
            \Database\Seeders\Polimedia\CourseContentSeeder::class,
        ]);
    }
}
