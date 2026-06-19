<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;

// Seeder contoh satu semester aktif (legacy/alternatif).
class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        Semester::create([
            'academic_year_id' => 1,
            'name' => 'Ganjil',
            'start_date' => '2025-08-01',
            'end_date' => '2026-01-31',
            'is_active' => true,
        ]);
    }
}
