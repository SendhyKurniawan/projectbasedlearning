<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Active Academic Year
        $academicYear = AcademicYear::factory()->active()->create([
            'year_start' => 2024,
            'year_end' => 2025,
        ]);

        // 2. Create Semesters (Ganjil is active)
        $ganjil = Semester::factory()->active()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $genap = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Genap',
            'start_date' => '2025-02-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        // 3. Create Lecturer Users
        $lecturers = [
            [
                'name' => 'Ahmad Fuadi, M.T.',
                'email' => 'ahmad@pjbl.test',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'is_active' => true,
            ],
            [
                'name' => 'Linda Permata, S.Kom.',
                'email' => 'linda@pjbl.test',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'is_active' => true,
            ],
            [
                'name' => 'Syarifuddin, Ph.D.',
                'email' => 'syarif@pjbl.test',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'is_active' => true,
            ],
        ];

        $createdLecturers = [];
        foreach ($lecturers as $lecturer) {
            $createdLecturers[] = User::create($lecturer);
        }

        // 4. Create Student Users
        $students = [];
        for ($i = 1; $i <= 15; $i++) {
            $students[] = User::create([
                'name' => "Student #$i",
                'email' => "student$i@pjbl.test",
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
                'nim' => '202400' . str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        // 5. Create Courses for Ganjil Semester
        $courses = [
            [
                'nama_matkul' => 'Algoritma dan Pemrograman',
                'kode_matkul' => 'IF101',
                'sks' => 4,
                'description' => 'Dasar-dasar logika pemrograman dan algoritma.',
            ],
            [
                'nama_matkul' => 'Pemrograman Berorientasi Objek',
                'kode_matkul' => 'IF201',
                'sks' => 3,
                'description' => 'Konsep OOP menggunakan Java atau PHP.',
            ],
            [
                'nama_matkul' => 'Jaringan Komputer',
                'kode_matkul' => 'IF301',
                'sks' => 3,
                'description' => 'Dasar-dasar jaringan komputer dan protokol.',
            ],
        ];

        foreach ($courses as $index => $courseData) {
            $course = Course::create([
                'nama_matkul' => $courseData['nama_matkul'],
                'kode_matkul' => $courseData['kode_matkul'],
                'sks' => $courseData['sks'],
                'description' => $courseData['description'],
                'dosen_id' => $createdLecturers[$index % count($createdLecturers)]->id,
                'semester_id' => $ganjil->id,
            ]);

            // Create some assignments for each course
            \App\Models\Assignment::create([
                'course_id' => $course->id,
                'title' => 'Tugas 1: ' . $course->nama_matkul,
                'description' => 'Deskripsi tugas 1 untuk ' . $course->nama_matkul,
                'type' => 'tugas',
                'deadline' => now()->addWeeks(1),
                'max_score' => 100,
            ]);

            \App\Models\Assignment::create([
                'course_id' => $course->id,
                'title' => 'Kuis 1: ' . $course->nama_matkul,
                'description' => 'Kuis pertama untuk ' . $course->nama_matkul,
                'type' => 'quiz',
                'deadline' => now()->addWeeks(2),
                'max_score' => 100,
            ]);

            // Enroll all students in each course
            foreach ($students as $student) {
                $course->students()->attach($student->id, [
                    'enrolled_at' => now(),
                ]);
            }
        }
    }
}
