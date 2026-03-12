<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'level',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }
}
