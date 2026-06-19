<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperQuizQuestion
 */
// Model satu soal quiz milik sebuah Assignment bertipe quiz.
// Jenis soal ditentukan kolom question_type (essay, pilihan_ganda, code_snippet).
class QuizQuestion extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_id',
        'question_text',
        'question_type', // essay, pilihan_ganda, code_snippet
        'correct_answer',
        'score_weight',
    ];

    // Tugas (quiz) pemilik soal ini.
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Pilihan jawaban (khusus soal pilihan ganda).
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }
}
