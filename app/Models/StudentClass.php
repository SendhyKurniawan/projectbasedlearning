<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_program_id',
        'semester_id',
        'name',
    ];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'student_class_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'student_class_id');
    }
}
