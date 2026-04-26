<?php

namespace Database\Seeders;

use App\Models\Discussion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscussionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('discussion_comments')->truncate();
        DB::table('discussions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $mahasiswa = User::where('email', 'mahasiswa@pjbl.test')->firstOrFail();
        $dosen     = User::where('email', 'dosen@pjbl.test')->firstOrFail();

        Discussion::create([
            'user_id' => $mahasiswa->id,
            'title'   => 'Diskusi Pemrograman Dasar',
            'content' => 'Bagaimana cara kerja variabel dalam pemrograman?',
            'topic'   => 'Umum',
        ]);

        Discussion::create([
            'user_id' => $dosen->id,
            'title'   => 'Pengumuman Jadwal Praktikum',
            'content' => 'Praktikum minggu depan dimajukan ke Senin pagi.',
            'topic'   => 'Akademik',
        ]);
    }
}
