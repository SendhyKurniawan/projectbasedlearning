<?php

namespace Database\Seeders\Polimedia;

use App\Models\Course;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\DB;

/**
 * Melengkapi 2 mata kuliah (baris Course sibling) + konten lengkap untuk setiap
 * kelas D di semua term, menyamakan kelas D dengan kelas A/B/C yang dibangun
 * CourseContentSeeder utama. Memakai ulang CourseContentSeeder::seedClassCourses
 * agar resep kontennya tetap di satu tempat.
 *
 * Idempoten: melewati kelas D yang sudah punya matkul, dan tidak menyentuh A/B/C.
 * TIDAK membuat mahasiswa atau mendaftarkan siapa pun — kelas D tetap kosong dari
 * enrollment sampai user nyata mendaftar sendiri (sesuai kondisi DB live saat ini).
 *
 * Sekali pakai, dijalankan manual (baris kelas D harus sudah ada):
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

                        continue; // tidak ada kelas D untuk prodi/term ini
                    }

                    if (Course::where('student_class_id', $class->id)->exists()) {
                        $skipped++;

                        continue; // sudah pernah di-seed — idempoten
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
