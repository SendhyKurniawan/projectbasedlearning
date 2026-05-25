<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        $flush = fn (self $course) => Cache::forget("sidebar:dosen:{$course->dosen_id}");
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

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

    protected ?\Illuminate\Support\Collection $cachedSiblings = null;

    /**
     * Other courses taught by the same dosen, same matkul code, same semester.
     * Used to populate kelas-target selectors and copy actions.
     * Result is memoized per instance so repeated calls within one request are free.
     */
    public function siblings(): \Illuminate\Support\Collection
    {
        return $this->cachedSiblings ??= static::where('dosen_id', $this->dosen_id)
            ->where('kode_matkul', $this->kode_matkul)
            ->where('semester_id', $this->semester_id)
            ->where('id', '!=', $this->id)
            ->with('studentClass')
            ->orderBy('student_class_id')
            ->get();
    }

    /**
     * Stable key for grouping sibling courses in views (dashboard, sidebar).
     * Group by nama_matkul so legacy courses with different codes still cluster.
     */
    public function getCourseGroupKeyAttribute(): string
    {
        return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
    }
}
