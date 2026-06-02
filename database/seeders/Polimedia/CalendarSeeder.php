<?php

namespace Database\Seeders\Polimedia;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic 4-year academic calendar: 4 academic years, each with a
 * Ganjil and Genap semester (8 semesters total). Only the latest Ganjil
 * (PolimediaData::ACTIVE_*) is active — the app expects exactly one active term.
 *
 * Replaces the single-term AcademicYearSeeder + SemesterSeeder.
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
