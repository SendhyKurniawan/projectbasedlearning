<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Seeder utama: titik masuk `php artisan db:seed`. Memanggil rangkaian seeder PoliMedia
// secara berurutan (kalender → struktur akademik → pengguna → konten matkul).
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Isi (seed) basis data aplikasi.
     */
    public function run(): void
    {
        $this->call([
            // Data nyata kampus PoliMedia (Politeknik Negeri Media Kreatif) Jakarta sebagai
            // lingkungan uji, mencakup kalender 4 tahun (8 semester). Regenerasi struktur:
            //   python scripts/pddikti/fetch_polimedia.py
            \Database\Seeders\Polimedia\CalendarSeeder::class,
            \Database\Seeders\Polimedia\StructureSeeder::class,
            \Database\Seeders\Polimedia\UsersSeeder::class,
            \Database\Seeders\Polimedia\CourseContentSeeder::class,
        ]);
    }
}
