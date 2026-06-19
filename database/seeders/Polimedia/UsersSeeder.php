<?php

namespace Database\Seeders\Polimedia;

use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun pengguna, semuanya sudah aktif sehingga bisa login tanpa langkah OTP:
 *   - 1 admin (akun bagikan)                                 [global]
 *   - 1 dosen per prodi (dosen prodi demo = akun bagikan)    [global, mengajar tiap term]
 *   - N mahasiswa per kelas, per semester                    [semua 8 term]
 *
 * Akun bagikan `mahasiswa@` memimpin kelas demo pada term aktif. Mahasiswa di-insert
 * massal (satu hash password bersama) agar ribuan baris tetap cepat dibuat.
 */
class UsersSeeder extends Seeder
{
    private const DOSEN_NAMES = [
        'Dr. Budi Santoso, M.Ds.', 'Prof. Siti Nurhaliza, M.Sn.', 'Ahmad Fauzi, M.Kom.',
        'Rina Wijayanti, M.Ds.', 'Dewi Lestari, M.Sn.', 'Hendra Gunawan, M.T.',
        'Maya Sari, M.Ikom.', 'Eko Prasetyo, M.Sn.', 'Lina Marlina, M.Ds.',
        'Fajar Nugroho, M.Kom.', 'Indah Permata, M.Sn.', 'Agus Salim, M.T.',
        'Citra Kirana, M.Ds.', 'Rizal Ramadhan, M.Ikom.', 'Wati Susanti, M.Sn.',
        'Bayu Aji, M.Ds.', 'Nadia Putri, M.Sn.', 'Yoga Pratama, M.Kom.',
    ];

    private const FIRST_NAMES = [
        'Andi', 'Bella', 'Citra', 'Dimas', 'Eka', 'Fina', 'Galih', 'Hana',
        'Irfan', 'Joko', 'Kirana', 'Lukman', 'Mira', 'Nanda', 'Oki', 'Putri',
        'Qori', 'Rama', 'Sinta', 'Tio', 'Umar', 'Vina', 'Wahyu', 'Yuni', 'Zaki',
    ];

    private const LAST_NAMES = [
        'Pratama', 'Wijaya', 'Lestari', 'Saputra', 'Anggraini', 'Hidayat',
        'Maulana', 'Safitri', 'Nugraha', 'Rahmawati', 'Firmansyah', 'Utami',
    ];

    public function run(): void
    {
        $now = now();
        // Hash password bersama sekali saja (bcrypt lambat); semua akun memakainya ulang.
        $passwordHash = Hash::make(PolimediaData::SHARED_PASSWORD);

        $this->seedStaff($passwordHash, $now);
        $studentCount = $this->seedStudents($passwordHash, $now);

        $this->command->info(sprintf(
            'PoliMedia users: 1 admin, %d dosen, %d mahasiswa.',
            StudyProgram::count(),
            $studentCount,
        ));
    }

    /** Admin + satu dosen per prodi (pakai Eloquent — cast 'hashed' membiarkan hash apa adanya). */
    private function seedStaff(string $passwordHash, $now): void
    {
        DB::transaction(function () use ($passwordHash, $now) {
            $base = fn (array $attrs) => array_merge([
                'password' => $passwordHash,
                'is_active' => true,
                'otp_verified_at' => $now,
                'email_verified_at' => $now,
            ], $attrs);

            User::firstOrCreate(
                ['email' => 'admin@' . PolimediaData::EMAIL_DOMAIN],
                $base(['name' => 'Administrator PoliMedia', 'role' => 'admin']),
            );

            foreach (StudyProgram::all() as $i => $program) {
                User::firstOrCreate(
                    ['email' => PolimediaData::dosenEmail($program->code)],
                    $base([
                        'name' => self::DOSEN_NAMES[$i % count(self::DOSEN_NAMES)],
                        'role' => 'dosen',
                        'nip' => '1985' . str_pad((string) $program->id, 4, '0', STR_PAD_LEFT),
                    ]),
                );
            }
        });
    }

    /** Insert massal mahasiswa untuk tiap kelas di tiap semester. */
    private function seedStudents(string $passwordHash, $now): int
    {
        $ts = $now->toDateTimeString();
        $programCodeById = StudyProgram::pluck('code', 'id');
        $semesters = Semester::with('academicYear')->get();

        $rows = [];
        $total = 0;
        $nameSeq = 0;

        $flush = function () use (&$rows) {
            if ($rows) {
                DB::table('users')->insert($rows);
                $rows = [];
            }
        };

        foreach ($semesters as $semester) {
            $yearStart = (int) $semester->academicYear->year_start;
            $term = $semester->name;
            $termDigit = $term === 'Ganjil' ? 1 : 2;
            $isActiveTerm = (bool) $semester->is_active;

            foreach ($programCodeById as $programId => $code) {
                $classes = StudentClass::where('study_program_id', $programId)
                    ->where('semester_id', $semester->id)
                    ->orderBy('name')->get();

                foreach ($classes as $idx => $class) {
                    // Kelas demo = kelas pertama prodi demo pada term aktif (untuk akun bagikan).
                    $isDemoClass = $isActiveTerm
                        && PolimediaData::isDemoProdi($code)
                        && $idx === PolimediaData::DEMO_KELAS_INDEX;

                    for ($seq = 1; $seq <= PolimediaData::STUDENTS_PER_KELAS; $seq++) {
                        $nim = sprintf('%d%d%02d%d%02d', $yearStart, $termDigit, $programId, $idx + 1, $seq);

                        if ($isDemoClass && $seq === 1) {
                            $email = 'mahasiswa@' . PolimediaData::EMAIL_DOMAIN;
                            $name = 'Mahasiswa Demo';
                        } else {
                            $email = $nim . '@' . PolimediaData::STUDENT_EMAIL_DOMAIN;
                            $name = self::FIRST_NAMES[$nameSeq % count(self::FIRST_NAMES)]
                                . ' ' . self::LAST_NAMES[$nameSeq % count(self::LAST_NAMES)];
                            $nameSeq++;
                        }

                        $rows[] = [
                            'name' => $name,
                            'email' => $email,
                            'role' => 'mahasiswa',
                            'nim' => $nim,
                            'student_class_id' => $class->id,
                            'password' => $passwordHash,
                            'is_active' => 1,
                            'otp_verified_at' => $ts,
                            'email_verified_at' => $ts,
                            'created_at' => $ts,
                            'updated_at' => $ts,
                        ];
                        $total++;

                        if (count($rows) >= 500) {
                            $flush();
                        }
                    }
                }
            }
        }

        $flush();

        return $total;
    }
}
