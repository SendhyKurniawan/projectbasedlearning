<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $dosen_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\User $dosen
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Material> $materials
 * @property-read int|null $materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDosenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
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

    public function materials()
    {
        return $this->hasMany(\App\Models\Material::class)->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\Assignment::class);
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
