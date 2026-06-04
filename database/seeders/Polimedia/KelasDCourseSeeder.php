<?php

namespace Database\Seeders\Polimedia;

use App\Models\Course;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\DB;

/**
 * Backfills the 2 mata kuliah (sibling Course rows) + full content for every
 * kelas D across all terms, bringing kelas D to parity with the A/B/C classes
 * the main CourseContentSeeder builds. Reuses CourseContentSeeder::seedClassCourses
 * so the content recipe stays in one place.
 *
 * Idempotent: skips any kelas D that already has courses, and never touches
 * A/B/C. Does NOT create students or enrol anyone — kelas D stay empty of
 * enrolment until real users self-register (matching the live DB's current state).
 *
 * One-off, run manually (kelas D rows must already exist):
 *   php artisan db:seed --class="Database\Seeders\Polimedia\KelasDCourseSeeder" --force
 */
class KelasDCourseSeeder extends CourseContentSeeder
{
    public function run(): void
    {
        $semesters = Semester::with('academicYear')->get();
        $programs = StudyProgram::with('department')->get();

        $created = 0;
        $skipped = 0;
        $missing = 0;

        DB::transaction(function () use ($semesters, $programs, &$created, &$skipped, &$missing) {
            foreach ($semesters as $semester) {
                $yearStart = (int) $semester->academicYear->year_start;

                foreach ($programs as $program) {
                    $abbr = PolimediaData::abbr($program->name);
                    $name = PolimediaData::className($abbr, 'D', $yearStart, $semester->name);

                    $class = StudentClass::where('study_program_id', $program->id)
                        ->where('semester_id', $semester->id)
                        ->where('name', $name)
                        ->first();

                    if (! $class) {
                        $missing++;

                        continue; // no kelas D for this prodi/term
                    }

                    if (Course::where('student_class_id', $class->id)->exists()) {
                        $skipped++;

                        continue; // already seeded — idempotent
                    }

                    $created += $this->seedClassCourses($program, $semester, $class, false);
                }
            }
        });

        $this->command->info(
            "Kelas D courses: {$created} created, {$skipped} kelas D already had courses, {$missing} prodi/term had no kelas D."
        );
    }
}
