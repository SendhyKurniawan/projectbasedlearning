<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Order matches tests/playwright/fixtures.ts USERS IDs exactly.
        // id=1
        \App\Models\User::create([
            'name'      => 'Admin Dusk',
            'email'     => 'admin@pjbl.test',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // id=2
        \App\Models\User::create([
            'name'      => 'Dosen Dusk',
            'email'     => 'dosen@pjbl.test',
            'password'  => bcrypt('password'),
            'role'      => 'dosen',
            'is_active' => true,
        ]);

        // id=3
        \App\Models\User::create([
            'name'           => 'Mahasiswa Dusk',
            'email'          => 'mahasiswa@pjbl.test',
            'password'       => bcrypt('password'),
            'role'           => 'mahasiswa',
            'is_active'      => true,
            'student_class_id' => 1,
        ]);

        // id=4 — inactive dosen (AUTH-03 test)
        \App\Models\User::create([
            'name'      => 'Pending Dosen',
            'email'     => 'pending@pjbl.test',
            'password'  => bcrypt('password'),
            'role'      => 'dosen',
            'is_active' => false,
        ]);

        // id=5
        \App\Models\User::create([
            'name'           => 'Other Mahasiswa',
            'email'          => 'other@pjbl.test',
            'password'       => bcrypt('password'),
            'role'           => 'mahasiswa',
            'is_active'      => true,
            'student_class_id' => 1,
        ]);

        // id=6
        \App\Models\User::create([
            'name'      => 'Other Dosen',
            'email'     => 'other.dosen@pjbl.test',
            'password'  => bcrypt('password'),
            'role'      => 'dosen',
            'is_active' => true,
        ]);
    }
}
