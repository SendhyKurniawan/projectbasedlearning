<?php

namespace Database\Seeders\Polimedia;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\Material;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds courses and all feature content on top of the PoliMedia structure, for
 * every semester (8 terms). Per prodi per term, each mata kuliah is created as
 * sibling Course rows (one per kelas, same dosen+kode+semester) so the multi-kelas
 * / copy-to-sibling feature has real data. Course titles carry the term's study
 * level (I..VIII) for curriculum flavour. Every course ships full content
 * (materials, all assignment types, a conference). The active-term demo kelas
 * additionally gets sample submissions. Global announcements + discussions are
 * seeded once.
 *
 * Runs after StructureSeeder + UsersSeeder.
 */
class CourseContentSeeder extends Seeder
{
    /** Two base mata kuliah per department; suffixed with the term's level. */
    private const COURSE_BASES = [
        'DSN' => ['Studio Desain', 'Teori dan Sejarah Desain'],
        'TGP' => ['Teknologi Grafika', 'Manajemen Produksi Cetak'],
        'PK' => ['Produksi Media', 'Teori Komunikasi'],
        'LAIN' => ['Praktik Industri Kreatif', 'Kewirausahaan Terapan'],
    ];

    public function run(): void
    {
        $semesters = Semester::with('academicYear')->get();
        $programs = StudyProgram::with('department')->get();

        // One transaction batches the many inserts (far faster than per-row commits).
        $courseCount = DB::transaction(fn () => $this->seedCourses($semesters, $programs));

        $this->seedAnnouncements();
        $this->seedDiscussions();

        $this->command->info("PoliMedia courses: {$courseCount} created with full content across {$semesters->count()} semesters.");
    }

    private function seedCourses($semesters, $programs): int
    {
        $courseCount = 0;

        foreach ($semesters as $semester) {
            $isActiveTerm = (bool) $semester->is_active;

            foreach ($programs as $program) {
                $classes = StudentClass::where('study_program_id', $program->id)
                    ->where('semester_id', $semester->id)
                    ->orderBy('name')->get();

                foreach ($classes as $idx => $class) {
                    // Only the active-term demo kelas (DG-A) gets sample submissions.
                    $withSamples = $isActiveTerm
                        && PolimediaData::isDemoProdi($program->code)
                        && $idx === PolimediaData::DEMO_KELAS_INDEX;

                    $courseCount += $this->seedClassCourses($program, $semester, $class, $withSamples);
                }
            }
        }

        return $courseCount;
    }

    /**
     * Create the prodi's mata kuliah for a single kelas — one sibling Course row
     * per base, each with full content (materials, assignments, conference) and
     * the kelas's students enrolled. Returns the number of courses created.
     *
     * Shared by the full seeder and KelasDCourseSeeder so the recipe lives once.
     */
    protected function seedClassCourses(StudyProgram $program, Semester $semester, StudentClass $class, bool $withSamples): int
    {
        $yearStart = (int) $semester->academicYear->year_start;
        $level = PolimediaData::level($yearStart, $semester->name);
        $roman = PolimediaData::roman($level);
        $deptCode = $program->department->code ?? 'LAIN';
        $bases = self::COURSE_BASES[$deptCode] ?? self::COURSE_BASES['LAIN'];

        $dosen = User::where('email', PolimediaData::dosenEmail($program->code))->first();
        if (! $dosen) {
            return 0;
        }

        $studentIds = User::where('student_class_id', $class->id)
            ->where('role', 'mahasiswa')->pluck('id');

        $created = 0;
        foreach ($bases as $i => $base) {
            $nama = "{$base} {$roman}";
            $kode = sprintf('%s%d%d', $deptCode, $level, $i + 1);

            $course = Course::create([
                'nama_matkul' => $nama,
                'kode_matkul' => $kode,
                'sks' => 3,
                'description' => "Mata kuliah {$nama} untuk program studi {$program->name}.",
                'dosen_id' => $dosen->id,
                'semester_id' => $semester->id,
                'student_class_id' => $class->id,
            ]);
            $created++;

            if ($studentIds->isNotEmpty()) {
                $course->students()->attach(
                    $studentIds->mapWithKeys(fn ($id) => [$id => ['enrolled_at' => now()]])->all()
                );
            }

            $this->addMaterials($course);
            $this->addAssignments($course);
            $this->addConference($course);

            if ($withSamples) {
                $this->addSampleSubmissions($course, $studentIds);
            }
        }

        return $created;
    }

    private function addMaterials(Course $course): void
    {
        Material::create([
            'course_id' => $course->id,
            'title' => 'Pengantar '.$course->nama_matkul,
            'content' => "Selamat datang di mata kuliah {$course->nama_matkul}. "
                .'Materi ini menjelaskan ruang lingkup, capaian pembelajaran, dan rencana perkuliahan.',
            'order' => 1,
        ]);

        Material::create([
            'course_id' => $course->id,
            'title' => 'Modul 1: Konsep Dasar',
            'content' => 'Modul pertama membahas konsep dasar dan istilah penting pada '
                .$course->nama_matkul.'. Bacalah sebelum mengerjakan tugas pertama.',
            'order' => 2,
        ]);
    }

    /**
     * Full set of assignment types — mirrors AssignmentSeeder's recipe:
     * tugas (pdf), tugas (url group), MC quiz, coding exercise, essay quiz.
     */
    private function addAssignments(Course $course): void
    {
        Assignment::create([
            'course_id' => $course->id,
            'title' => 'Tugas 1: Resume Materi '.$course->nama_matkul,
            'description' => 'Silakan buat rangkuman dari pertemuan pertama hingga ketiga dalam format PDF (Maksimal 10MB).',
            'type' => 'tugas',
            'submission_format' => 'pdf',
            'assignment_number' => 1,
            'order' => 1,
            'max_score' => 100,
            'deadline' => now()->addDays(5),
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'title' => 'Project Akhir: Implementasi '.$course->nama_matkul,
            'description' => 'Kerjakan project akhir secara berkelompok. Kumpulkan Link Repository Github atau Google Drive berisi karya dan laporan.',
            'type' => 'tugas',
            'submission_format' => 'url',
            'assignment_number' => 2,
            'order' => 2,
            'max_score' => 100,
            'deadline' => now()->addWeeks(2),
            'is_group' => true,
            'max_group_size' => 4,
        ]);

        $quizPG = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Kuis Tengah Semester: '.$course->nama_matkul,
            'description' => 'Kuis pilihan ganda evaluasi tengah semester. Durasi 60 menit.',
            'type' => 'quiz',
            'quiz_number' => 1,
            'order' => 3,
            'max_score' => 100,
            'duration_minutes' => 60,
            'deadline' => now()->addDays(7),
        ]);

        $q1 = QuizQuestion::create([
            'assignment_id' => $quizPG->id,
            'question_text' => 'Manakah pernyataan paling tepat mengenai materi mata kuliah ini?',
            'question_type' => 'pilihan_ganda',
            'score_weight' => 50,
        ]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pernyataan yang sesuai.', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pengecoh pertama.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pengecoh kedua.', 'is_correct' => false]);

        $q2 = QuizQuestion::create([
            'assignment_id' => $quizPG->id,
            'question_text' => 'Apa tujuan utama dari proses yang dipelajari sejauh ini?',
            'question_type' => 'pilihan_ganda',
            'score_weight' => 50,
        ]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Menghasilkan karya berkualitas.', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Memperbanyak kesalahan.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Mengabaikan standar.', 'is_correct' => false]);

        Assignment::create([
            'course_id' => $course->id,
            'title' => 'Exercise 1: Logika Dasar',
            'description' => 'Lengkapi potongan kode berikut agar menghasilkan nilai true.',
            'type' => 'exercise',
            'assignment_number' => 3,
            'order' => 4,
            'max_score' => 100,
            'deadline' => now()->addDays(3),
            'exercise_config' => [
                'language' => 'javascript',
                'starter_code' => "function checkLogic() {\n   // Tulis kondisi dan return nilai true\n   \n}\n\nconsole.log(checkLogic());",
                'required_keywords' => ['return', 'true'],
                'hints' => [
                    'Gunakan syntax return true;',
                    'Cek console untuk melihat hasil Output preview.',
                ],
            ],
        ]);

        $quizEssay = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Kuis Pemahaman Teori',
            'description' => 'Kuis berformat essay bebas waktu. Uraikan dengan bahasa Anda sendiri.',
            'type' => 'quiz',
            'quiz_number' => 2,
            'order' => 5,
            'max_score' => 100,
            'deadline' => now()->addDays(10),
        ]);
        QuizQuestion::create([
            'assignment_id' => $quizEssay->id,
            'question_text' => 'Jelaskan penerapan teori '.$course->nama_matkul.' di dunia industri.',
            'question_type' => 'essay',
            'score_weight' => 100,
        ]);
    }

    private function addConference(Course $course): void
    {
        Conference::create([
            'course_id' => $course->id,
            'dosen_id' => $course->dosen_id,
            'title' => 'Kuliah Daring: '.$course->nama_matkul,
            'description' => 'Sesi tatap muka daring membahas materi mingguan. Mahasiswa wajib hadir.',
            'room_name' => 'polimedia-'.Str::lower(Str::random(12)),
            'scheduled_at' => now()->addDays(2)->setTime(9, 0),
            'status' => 'scheduled',
        ]);
    }

    /** A few submissions on the first tugas so the grading view has data. */
    private function addSampleSubmissions(Course $course, $studentIds): void
    {
        $tugas = $course->assignments()->where('type', 'tugas')
            ->where('submission_format', 'pdf')->first();
        if (! $tugas) {
            return;
        }

        $ids = $studentIds->take(3)->values();
        $states = [
            ['status' => 'graded', 'score' => 88, 'feedback' => 'Kerja bagus, rangkuman lengkap.'],
            ['status' => 'submitted', 'score' => null, 'feedback' => null],
            ['status' => 'late', 'score' => null, 'feedback' => null],
        ];

        foreach ($ids as $i => $id) {
            $state = $states[$i] ?? $states[1];
            Submission::create(array_merge([
                'assignment_id' => $tugas->id,
                'mahasiswa_id' => $id,
                'notes' => 'Berikut resume materi yang telah saya kerjakan.',
                'submitted_at' => now()->subDay(),
                'auto_graded' => false,
            ], $state));
        }
    }

    private function seedAnnouncements(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            return;
        }

        $items = [
            ['Selamat Datang di PBL Workspace PoliMedia', 'Platform pembelajaran berbasis proyek resmi Politeknik Negeri Media Kreatif kini aktif. Silakan jelajahi setiap fitur.'],
            ['Jadwal Perkuliahan Semester Ganjil', 'Perkuliahan semester ganjil dimulai pekan ini. Cek menu Jadwal untuk konferensi dan tenggat tugas Anda.'],
            ['Panduan Pengumpulan Tugas', 'Pastikan mengumpulkan tugas sebelum tenggat. Format PDF maksimal 10MB atau tautan untuk tugas berbasis URL.'],
        ];

        foreach ($items as [$title, $content]) {
            Announcement::create([
                'user_id' => $admin->id,
                'title' => $title,
                'content' => $content,
                'target_audience' => 'all',
            ]);
        }
    }

    private function seedDiscussions(): void
    {
        $dosen = User::where('role', 'dosen')->first();
        $students = User::where('role', 'mahasiswa')->limit(4)->get();
        if (! $dosen || $students->count() < 2) {
            return;
        }

        $threads = [
            ['Tips Mengerjakan Project Akhir', 'Umum', 'Bagikan tips dan kendala kalian saat mengerjakan project akhir di sini.'],
            ['Rekomendasi Software Desain Gratis', 'Desain', 'Adakah rekomendasi software desain gratis untuk mahasiswa baru?'],
            ['Pertanyaan Seputar Kuis Tengah Semester', 'Akademik', 'Apakah kuis tengah semester boleh dikerjakan ulang jika koneksi terputus?'],
        ];

        foreach ($threads as $idx => [$title, $topic, $content]) {
            $author = $idx === 0 ? $dosen : $students[$idx % $students->count()];
            $discussion = Discussion::create([
                'user_id' => $author->id,
                'title' => $title,
                'topic' => $topic,
                'content' => $content,
            ]);

            DiscussionComment::create([
                'discussion_id' => $discussion->id,
                'user_id' => $students[0]->id,
                'content' => 'Terima kasih, ini sangat membantu!',
            ]);
            DiscussionComment::create([
                'discussion_id' => $discussion->id,
                'user_id' => $dosen->id,
                'content' => 'Silakan lanjutkan diskusi dengan sopan dan saling membantu.',
            ]);
        }
    }
}
