<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $content
 * @property string|null $file_path
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Material extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'content',
        'file_path',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }
    
    public function views()
    {
        return $this->hasMany(MaterialView::class);
    }
    
    // Helper Methods
    public function hasBeenViewedBy($studentId)
    {
        return $this->views()->where('student_id', $studentId)->exists();
    }
}
