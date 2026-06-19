<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

// Seeder contoh satu tahun ajaran aktif (legacy/alternatif).
class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'year_start' => 2025,
            'year_end' => 2026,
            'is_active' => true,
        ]);
    }
}
