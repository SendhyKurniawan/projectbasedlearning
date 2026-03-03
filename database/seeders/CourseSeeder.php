<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get dosen users
        $dosen1 = \App\Models\User::where('email', 'budi.dosen@pjbl.test')->first();
        $dosen2 = \App\Models\User::where('email', 'siti.dosen@pjbl.test')->first();

        // Create Courses
        $course1 = \App\Models\Course::create([
            'nama_matkul' => 'Pengembangan Website Dasar',
            'kode_matkul' => 'WEB101',
            'sks' => 3,
            'description' => 'Mata kuliah dasar pengembangan website menggunakan HTML, CSS, dan JavaScript',
            'dosen_id' => $dosen1->id,
        ]);

        $course2 = \App\Models\Course::create([
            'nama_matkul' => 'Sistem Basis Data',
            'kode_matkul' => 'DB201',
            'sks' => 4,
            'description' => 'Mata kuliah sistem basis data relasional dan SQL',
            'dosen_id' => $dosen2->id,
        ]);

        // Create Materials for Course 1
        \App\Models\Material::create([
            'course_id' => $course1->id,
            'title' => 'Pengenalan HTML',
            'content' => '<h1>HTML Dasar</h1><p>HTML adalah bahasa markup untuk membuat halaman web.</p>',
            'order' => 1,
        ]);

        \App\Models\Material::create([
            'course_id' => $course1->id,
            'title' => 'CSS Fundamentals',
            'content' => '<h1>CSS Dasar</h1><p>CSS digunakan untuk styling halaman web.</p>',
            'order' => 2,
        ]);

        // Create Materials for Course 2
        \App\Models\Material::create([
            'course_id' => $course2->id,
            'title' => 'Pengenalan Database',
            'content' => '<h1>Sistem Basis Data</h1><p>Database adalah kumpulan data yang terorganisir.</p>',
            'order' => 1,
        ]);

        // Create Assignments for Course 1
        \App\Models\Assignment::create([
            'course_id' => $course1->id,
            'title' => 'Tugas 1: Membuat Halaman HTML Sederhana',
            'description' => 'Buat halaman HTML dengan struktur dasar',
            'type' => 'tugas',
            'deadline' => now()->addDays(7),
            'max_score' => 100,
        ]);

        \App\Models\Assignment::create([
            'course_id' => $course1->id,
            'title' => 'Tugas 2: Styling dengan CSS',
            'description' => 'Buat halaman web dengan styling CSS',
            'type' => 'tugas',
            'deadline' => now()->addDays(14),
            'max_score' => 100,
        ]);

        // Enroll students to courses
        $students = \App\Models\User::where('role', 'mahasiswa')->get();
        foreach ($students as $student) {
            $course1->students()->attach($student->id, [
                'enrolled_at' => now(),
            ]);
            if ($student->id % 2 == 0) {
                $course2->students()->attach($student->id, [
                    'enrolled_at' => now(),
                ]);
            }
        }
    }
}
