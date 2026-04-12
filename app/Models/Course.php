<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $nama_matkul
 * @property string $kode_matkul
 * @property string|null $description
 * @property int $dosen_id
 * @property int|null $semester_id
 * @property int|null $student_class_id
 * @property string|null $course_img
 * @property int $sks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\User $dosen
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Material> $materials
 * @property-read int|null $materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $students
 * @property-read int|null $students_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conference> $conferences
 * @property-read int|null $conferences_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @mixin \Eloquent
 * @mixin IdeHelperCourse
 */
class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_matkul',
        'kode_matkul',
        'description',
        'dosen_id',
        'sks',
        'semester_id',
        'student_class_id',
        'course_img',
    ];

    // Relationships
    public function dosen()
    {
        return $this->belongsTo(\App\Models\User::class, 'dosen_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(\App\Models\StudentClass::class, 'student_class_id');
    }

    public function materials()
    {
        return $this->hasMany(\App\Models\Material::class)->orderBy('order');
    }

    public function assignments()
    {
        // Order by the 'order' column on the assignments table itself.
        // The xxxx_create_course_assignment_order_table pivot approach was never applied.
        return $this->hasMany(Assignment::class)->orderBy('order');
    }

    public function students()
    {
        return $this->belongsToMany(\App\Models\User::class, 'enrollments', 'course_id', 'mahasiswa_id')
            ->withPivot('final_grade', 'enrolled_at')
            ->withTimestamps();
    }

    public function conferences()
    {
        return $this->hasMany(\App\Models\Conference::class);
    }
}
