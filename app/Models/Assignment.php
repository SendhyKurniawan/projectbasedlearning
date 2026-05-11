<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $deadline
 * @property int $max_score
 * @property string $type
 * @property array<array-key, mixed>|null $exercise_config
 * @property int|null $order
 * @property int|null $assignment_number
 * @property int|null $quiz_number
 * @property int|null $duration_minutes
 * @property string|null $submission_format
 * @property int|null $required_material_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submission> $submissions
 * @property-read int|null $submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuizQuestion> $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \App\Models\Material|null $requiredMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment query()
 * @method static \Database\Factories\AssignmentFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 * @mixin IdeHelperAssignment
 */
class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_number',
        'submission_format',
        'course_id',
        'title',
        'order',
        'description',
        'deadline',
        'max_score',
        'type', // tugas, quiz, exercise
        'exercise_config',
        'required_material_id',
        'duration_minutes',
        'quiz_number',
        'is_group',
        'max_group_size',
        'grading_mode',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'exercise_config' => 'array',
            'is_group' => 'boolean',
        ];
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function submissions()
    {
        return $this->hasMany(\App\Models\Submission::class);
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\QuizQuestion::class);
    }

    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }

    public function requiredMaterial()
    {
        return $this->belongsTo(Material::class, 'required_material_id');
    }

    // Helper Methods

    /**
     * Check whether the assignment is accessible to a given student.
     * Returns true if there is no prerequisite material, or if the student
     * has already recorded a view on that material.
     */
    public function isUnlockedFor($studentId): bool
    {
        if (!$this->required_material_id) {
            return true;
        }

        return MaterialView::where('material_id', $this->required_material_id)
            ->where('student_id', $studentId)
            ->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('deadline', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('deadline', '<', now());
    }
}
