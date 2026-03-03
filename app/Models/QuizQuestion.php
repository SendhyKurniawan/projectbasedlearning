<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperQuizQuestion
 */
class QuizQuestion extends Model
{
    protected $fillable = [
        'assignment_id',
        'question_text',
        'question_type', // essay, pilihan_ganda, code_snippet
        'correct_answer',
        'score_weight',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }
}
