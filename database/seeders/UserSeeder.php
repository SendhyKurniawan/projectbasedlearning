<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Seeder akun contoh (legacy/alternatif): 1 admin, beberapa dosen, dan beberapa mahasiswa.
class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Buat akun Admin
        \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@pjbl.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Buat akun Dosen
        \App\Models\User::create([
            'name' => 'Dosen Utama',
            'email' => 'dosen@pjbl.test',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        \App\Models\User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi.dosen@pjbl.test',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        \App\Models\User::create([
            'name' => 'Prof. Siti Nurhaliza',
            'email' => 'siti.dosen@pjbl.test',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        // Buat akun Mahasiswa
        \App\Models\User::create([
            'name' => 'Mahasiswa Utama',
            'email' => 'mahasiswa@pjbl.test',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'student_class_id' => 1,
        ]);

        $students = [
            ['name' => 'Ahmad Rizky', 'email' => 'ahmad.mhs@pjbl.test'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.mhs@pjbl.test'],
            ['name' => 'Cahya Pratama', 'email' => 'cahya.mhs@pjbl.test'],
            ['name' => 'Rina Wijaya', 'email' => 'rina.mhs@pjbl.test'],
            ['name' => 'Fajar Kurniawan', 'email' => 'fajar.mhs@pjbl.test'],
        ];

        foreach ($students as $student) {
            \App\Models\User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => bcrypt('password'),
                'role' => 'mahasiswa',
                'student_class_id' => rand(1, 2),
            ]);
        }
    }
}
