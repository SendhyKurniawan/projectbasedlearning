<?php

namespace Database\Seeders\Polimedia;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

/**
 * Mengisi kalender akademik 4 tahun yang realistis: 4 tahun ajaran, masing-masing
 * dengan semester Ganjil & Genap (total 8 semester). Hanya Ganjil terbaru
 * (PolimediaData::ACTIVE_*) yang aktif — aplikasi mengharuskan tepat satu term aktif.
 *
 * Menggantikan AcademicYearSeeder + SemesterSeeder yang hanya satu term.
 */
class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PolimediaData::ACADEMIC_YEARS as [$start, $end]) {
            $year = AcademicYear::firstOrCreate(
                ['year_start' => $start, 'year_end' => $end],
                ['is_active' => $start === PolimediaData::ACTIVE_YEAR_START],
            );

            foreach (PolimediaData::TERMS as $term) {
                [$startDate, $endDate] = PolimediaData::termDates($start, $end, $term);

                Semester::firstOrCreate(
                    ['academic_year_id' => $year->id, 'name' => $term],
                    [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'is_active' => PolimediaData::isActiveTerm($start, $term),
                    ],
                );
            }
        }

        $this->command->info(sprintf(
            'PoliMedia calendar: %d academic years, %d semesters (active: %s %d/%d).',
            count(PolimediaData::ACADEMIC_YEARS),
            count(PolimediaData::ACADEMIC_YEARS) * count(PolimediaData::TERMS),
            PolimediaData::ACTIVE_TERM,
            PolimediaData::ACTIVE_YEAR_START,
            PolimediaData::ACTIVE_YEAR_START + 1,
        ));
    }
}
