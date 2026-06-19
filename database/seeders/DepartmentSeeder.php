<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

// Seeder contoh data jurusan (seeder legacy/alternatif, di luar rangkaian PoliMedia).
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::insert([
            ['name' => 'Teknik Informatika dan Komputer', 'code' => 'TIK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Teknik Elektro', 'code' => 'TE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
