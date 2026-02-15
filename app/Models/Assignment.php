<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $deadline
 * @property int $max_score
 * @property string $type
 * @property array<array-key, mixed>|null $exercise_config
 * @property bool $auto_grade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submission> $submissions
 * @property-read int|null $submissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereAutoGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereExerciseConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereMaxScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Assignment extends Model
{
    protected $fillable = [
        'assignment_number',
        'submission_format',
        'course_id',
        'title',
        'description',
        'deadline',
        'max_score',
        'type', // tugas, quiz, project
        'exercise_config',
        'auto_grade',
        'required_material_id',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'exercise_config' => 'array',
            'auto_grade' => 'boolean',
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

    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }
    
    public function requiredMaterial()
    {
        return $this->belongsTo(Material::class, 'required_material_id');
    }
    
    // Helper Methods
    public function isUnlockedFor($studentId)
    {
        // If no prerequisite material, assignment is always unlocked
        if (!$this->required_material_id) {
            return true;
        }
        
        // Check if student has viewed the required material
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
