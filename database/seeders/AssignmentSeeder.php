<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Submission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder contoh tugas (legacy/alternatif): membuat satu set lengkap tugas/quiz/exercise
// untuk SETIAP matkul yang ada (tugas PDF, project URL, quiz pilihan ganda, exercise, quiz essay).
class AssignmentSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // 1. Bersihkan tugas lama beserta dependensinya.
        // Karena foreign key, mengosongkan Assignments akan ikut mengosongkan Submissions & Quiz.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Submission::truncate();
        QuizOption::truncate();
        QuizQuestion::truncate();
        Assignment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $courses = Course::all();

        foreach ($courses as $course) {
            
            // --- 1. TUGAS (UPLOAD FILE PDF) ---
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Tugas 1: Resume Materi ' . $course->nama_matkul,
                'description' => 'Silakan buat rangkuman dari pertemuan pertama hingga ketiga dalam format PDF (Maksimal 10MB).',
                'type' => 'tugas',
                'submission_format' => 'pdf',
                'assignment_number' => 1,
                'max_score' => 100,
                'deadline' => now()->addDays(5),
            ]);

            // --- 2. TUGAS KELOMPOK/PROJECT (UPLOAD LINK URL) ---
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Project Akhir: Implementasi ' . $course->nama_matkul,
                'description' => 'Kerjakan project akhir sesuai dengan kelompok masing-masing. Kumpulkan Link Repository Github atau Link Google Drive yang berisi source code dan laporan.',
                'type' => 'tugas',
                'submission_format' => 'url',
                'assignment_number' => 2,
                'max_score' => 100,
                'deadline' => now()->addWeeks(2),
            ]);

            // --- 3. QUIZ (PILIHAN GANDA DENGAN DURASI) ---
            $quizPG = Assignment::create([
                'course_id' => $course->id,
                'title' => 'Kuis Tengah Semester: ' . $course->nama_matkul,
                'description' => 'Kuis ini berisi soal pilihan ganda evaluasi tengah semester. Durasi pengerjaan adalah 60 Menit.',
                'type' => 'quiz',
                'quiz_number' => 1,
                'max_score' => 100,
                'duration_minutes' => 60,
                'deadline' => now()->addDays(7),
            ]);

            // Buat beberapa soal contoh untuk quiz pilihan ganda
            $q1 = QuizQuestion::create([
                'assignment_id' => $quizPG->id,
                'question_text' => 'Manakah di bawah ini yang merupakan pernyataan paling tepat mengenai materi mata kuliah ini?',
                'question_type' => 'pilihan_ganda',
                'score_weight' => 50,
            ]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pernyataan benar yang sesuai.', 'is_correct' => true]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pengecoh pertama.', 'is_correct' => false]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Pengecoh kedua.', 'is_correct' => false]);

            $q2 = QuizQuestion::create([
                'assignment_id' => $quizPG->id,
                'question_text' => 'Apa tujuan utama dari proses yang telah kita pelajari sejauh ini?',
                'question_type' => 'pilihan_ganda',
                'score_weight' => 50,
            ]);
            QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Mengoptimalkan performa.', 'is_correct' => true]);
            QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Memperbanyak bug.', 'is_correct' => false]);
            QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Meminimalisir fungsi.', 'is_correct' => false]);

            // --- 4. EXERCISE (LATIHAN CODING) ---
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Exercise 1: Logic Dasar',
                'description' => 'Lengkapilah potongan kode berikut (jika relevan dengan matkul Praktek) agar menghasilkan nilai True.',
                'type' => 'exercise',
                'assignment_number' => 3,
                'max_score' => 100,
                'deadline' => now()->addDays(3),
                'exercise_config' => [
                    'language' => 'javascript',
                    'starter_code' => "function checkLogic() {\n   // Tulis kondisi if dan return nilai true\n   \n}\n\nconsole.log(checkLogic());",
                    'hints' => [
                        'Gunakan syntax return true;',
                        'Jangan lupa cek console untuk melihat hasil Output preview.'
                    ]
                ],
            ]);

             // --- 5. QUIZ (ESSAY TANPA BATAS WAKTU) ---
             $quizEssay = Assignment::create([
                'course_id' => $course->id,
                'title' => 'Kuis Pemahaman Teori',
                'description' => 'Kuis berformat essay bebas waktu. Uraikan dengan bahasa Anda sendiri.',
                'type' => 'quiz',
                'quiz_number' => 2,
                'max_score' => 100,
                'deadline' => now()->addDays(10),
            ]);
            
            QuizQuestion::create([
                'assignment_id' => $quizEssay->id,
                'question_text' => 'Jelaskan seberapa jauh Anda memahami implementasi teori ' . $course->nama_matkul . ' di dunia industri sesungguhnya?',
                'question_type' => 'essay',
                'score_weight' => 100,
            ]);

        }
    }
}
