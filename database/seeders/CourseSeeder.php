<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $dosen      = User::where('email', 'dosen@pjbl.test')->firstOrFail();
        $otherDosen = User::where('email', 'other.dosen@pjbl.test')->firstOrFail();
        $mahasiswa  = User::where('email', 'mahasiswa@pjbl.test')->firstOrFail();
        $otherMhs   = User::where('email', 'other@pjbl.test')->firstOrFail();

        // id=1 — main course used by most tests
        $course1 = Course::create([
            'nama_matkul'     => 'Pemrograman Dasar',
            'kode_matkul'     => 'IF101',
            'sks'             => 3,
            'description'     => 'Dasar-dasar logika pemrograman.',
            'dosen_id'        => $dosen->id,
            'student_class_id' => 1,
            'semester_id'     => 1,
        ]);
        $course1->students()->attach($mahasiswa->id, ['enrolled_at' => now()]);
        $course1->students()->attach($otherMhs->id,  ['enrolled_at' => now()]);

        // id=1 — material "Modul 1" (with content)
        Material::create([
            'course_id' => $course1->id,
            'title'     => 'Modul 1',
            'content'   => '<h1>Modul 1</h1><p>Pengantar pemrograman dasar.</p>',
            'order'     => 1,
        ]);

        // id=2 — material "Modul 2" (no file attachment, CRS-07)
        Material::create([
            'course_id' => $course1->id,
            'title'     => 'Modul 2',
            'content'   => '<h1>Modul 2</h1><p>Materi lanjutan pemrograman.</p>',
            'order'     => 2,
        ]);

        // id=2 — Dasar Jaringan (mahasiswa NOT enrolled — CRS-03 enroll test)
        $course2 = Course::create([
            'nama_matkul'     => 'Dasar Jaringan',
            'kode_matkul'     => 'IF202',
            'sks'             => 3,
            'description'     => 'Dasar-dasar jaringan komputer.',
            'dosen_id'        => $otherDosen->id,
            'student_class_id' => 1,
            'semester_id'     => 1,
        ]);
        $course2->students()->attach($otherMhs->id, ['enrolled_at' => now()]);
    }
}
