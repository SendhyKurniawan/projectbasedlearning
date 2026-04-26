<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Submission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Submission::truncate();
        QuizOption::truncate();
        QuizQuestion::truncate();
        Assignment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $course1 = Course::where('kode_matkul', 'IF101')->firstOrFail();

        // id=1 — tugasPdf
        Assignment::create([
            'course_id'         => $course1->id,
            'title'             => 'Tugas PDF',
            'description'       => 'Kumpulkan resume materi dalam format PDF.',
            'type'              => 'tugas',
            'submission_format' => 'pdf',
            'assignment_number' => 1,
            'max_score'         => 100,
            'deadline'          => now()->addDays(7),
        ]);

        // id=2 — tugasUrl
        Assignment::create([
            'course_id'         => $course1->id,
            'title'             => 'Tugas URL',
            'description'       => 'Kumpulkan link repository project.',
            'type'              => 'tugas',
            'submission_format' => 'url',
            'assignment_number' => 2,
            'max_score'         => 100,
            'deadline'          => now()->addDays(14),
        ]);

        // id=3 — tugasTerkunci (requires viewing Modul 1 first)
        Assignment::create([
            'course_id'            => $course1->id,
            'title'                => 'Tugas Terkunci',
            'description'          => 'Tugas ini terkunci sampai Anda membaca Modul 1.',
            'type'                 => 'tugas',
            'submission_format'    => 'pdf',
            'assignment_number'    => 3,
            'max_score'            => 100,
            'deadline'             => now()->addDays(10),
            'required_material_id' => 1,
        ]);

        // id=4 — quizCepat
        $quizCepat = Assignment::create([
            'course_id'        => $course1->id,
            'title'            => 'Quiz Cepat',
            'description'      => 'Kuis singkat 30 menit.',
            'type'             => 'quiz',
            'quiz_number'      => 1,
            'max_score'        => 100,
            'duration_minutes' => 30,
            'deadline'         => now()->addDays(7),
        ]);

        // quiz_questions id=1 (mc)
        $mc = QuizQuestion::create([
            'assignment_id' => $quizCepat->id,
            'question_text' => 'Manakah pernyataan yang benar tentang pemrograman?',
            'question_type' => 'pilihan_ganda',
            'score_weight'  => 50,
        ]);
        QuizOption::create(['question_id' => $mc->id, 'option_text' => 'Program harus memiliki logika.', 'is_correct' => true]);
        QuizOption::create(['question_id' => $mc->id, 'option_text' => 'Program tidak membutuhkan variabel.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $mc->id, 'option_text' => 'Bug tidak perlu diperbaiki.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $mc->id, 'option_text' => 'Kompiler bukan termasuk software.', 'is_correct' => false]);

        // quiz_questions id=2 (essay)
        QuizQuestion::create([
            'assignment_id' => $quizCepat->id,
            'question_text' => 'Jelaskan perbedaan antara variabel dan konstanta!',
            'question_type' => 'essay',
            'score_weight'  => 50,
        ]);

        // id=5 — exerciseHtml
        Assignment::create([
            'course_id'        => $course1->id,
            'title'            => 'Exercise HTML',
            'description'      => 'Buat halaman HTML sederhana.',
            'type'             => 'exercise',
            'assignment_number' => 4,
            'max_score'        => 100,
            'deadline'         => now()->addDays(7),
            'exercise_config'  => [
                'language'     => 'htmlmixed',
                'starter_code' => "<!DOCTYPE html>\n<html>\n<body>\n  <h1>Hello</h1>\n</body>\n</html>",
                'hints'        => ['Gunakan tag <h1> untuk judul.'],
            ],
        ]);

        // Generic assignments for remaining courses
        foreach (Course::where('kode_matkul', '!=', 'IF101')->get() as $course) {
            Assignment::create([
                'course_id'         => $course->id,
                'title'             => 'Tugas 1: Resume ' . $course->nama_matkul,
                'description'       => 'Buat rangkuman materi.',
                'type'              => 'tugas',
                'submission_format' => 'pdf',
                'assignment_number' => 1,
                'max_score'         => 100,
                'deadline'          => now()->addDays(7),
            ]);

            $quiz = Assignment::create([
                'course_id'        => $course->id,
                'title'            => 'Kuis ' . $course->nama_matkul,
                'description'      => 'Kuis evaluasi.',
                'type'             => 'quiz',
                'quiz_number'      => 1,
                'max_score'        => 100,
                'duration_minutes' => 60,
                'deadline'         => now()->addDays(7),
            ]);

            $q = QuizQuestion::create([
                'assignment_id' => $quiz->id,
                'question_text' => 'Soal pilihan ganda untuk ' . $course->nama_matkul,
                'question_type' => 'pilihan_ganda',
                'score_weight'  => 100,
            ]);
            QuizOption::create(['question_id' => $q->id, 'option_text' => 'Jawaban benar.', 'is_correct' => true]);
            QuizOption::create(['question_id' => $q->id, 'option_text' => 'Pengecoh.', 'is_correct' => false]);
        }
    }
}
