<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $student_id
 * @property string|null $file_path
 * @property string|null $notes
 * @property string|null $code_answer
 * @property array<array-key, mixed>|null $validation_result
 * @property bool $auto_graded
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property int|null $score
 * @property string|null $feedback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Assignment $assignment
 * @property-read bool $is_graded
 * @property-read \App\Models\User $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereAssignmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereAutoGraded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereCodeAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereValidationResult($value)
 * @mixin \Eloquent
 */
class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'mahasiswa_id',
        'file_path',
        'url_link',
        'notes',
        'submitted_at',
        'score',
        'feedback',
        'status', // submitted, late, graded
        'code_answer',
        'validation_result',
        'auto_graded',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'validation_result' => 'array',
            'auto_graded' => 'boolean',
        ];
    }

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(\App\Models\Assignment::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(\App\Models\User::class, 'mahasiswa_id');
    }

    // Accessors
    public function getIsGradedAttribute(): bool
    {
        return $this->score !== null;
    }
}
