<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperQuizOption
 */
// Model satu opsi jawaban untuk soal pilihan ganda; is_correct menandai opsi yang benar.
class QuizOption extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
    ];

    // is_correct disimpan sebagai boolean.
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // Soal pemilik opsi ini.
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
