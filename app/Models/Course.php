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
// Model mata kuliah (course). Satu baris Course = satu kelas dari sebuah matkul.
// Dosen yang mengajar matkul sama (kode_matkul + semester) ke beberapa kelas akan
// punya beberapa baris Course; baris-baris itu disebut "siblings".
class Course extends Model
{
    use HasFactory;

    // Saat course dibuat/diubah/dihapus, kosongkan cache sidebar milik dosen terkait
    // agar daftar matkul pada sidebar selalu sinkron dengan data terbaru.
    protected static function booted(): void
    {
        $flush = fn (self $course) => Cache::forget("sidebar:dosen:{$course->dosen_id}");
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    // Kolom yang boleh diisi secara massal (mass assignment).
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

    // Relasi
    // Dosen pengampu matkul ini.
    public function dosen()
    {
        return $this->belongsTo(\App\Models\User::class, 'dosen_id');
    }

    // Semester tempat matkul ini berjalan.
    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class);
    }

    // Kelas (rombongan belajar) yang mengikuti matkul ini.
    public function studentClass()
    {
        return $this->belongsTo(\App\Models\StudentClass::class, 'student_class_id');
    }

    // Materi pembelajaran milik matkul, diurutkan sesuai kolom 'order'.
    public function materials()
    {
        return $this->hasMany(\App\Models\Material::class)->orderBy('order');
    }

    public function assignments()
    {
        // Urutkan langsung berdasarkan kolom 'order' pada tabel assignments.
        // Pendekatan pivot xxxx_create_course_assignment_order_table tidak pernah dipakai.
        return $this->hasMany(Assignment::class)->orderBy('order');
    }

    // Mahasiswa yang terdaftar (enroll) di matkul ini, via tabel pivot enrollments.
    public function students()
    {
        return $this->belongsToMany(\App\Models\User::class, 'enrollments', 'course_id', 'mahasiswa_id')
            ->withPivot('final_grade', 'enrolled_at')
            ->withTimestamps();
    }

    // Sesi konferensi (Jitsi) yang dijadwalkan pada matkul ini.
    public function conferences()
    {
        return $this->hasMany(\App\Models\Conference::class);
    }

    // Cache siblings per instance agar tidak query berulang dalam satu request.
    protected ?\Illuminate\Support\Collection $cachedSiblings = null;

    /**
     * Course "siblings": matkul lain yang diampu dosen yang sama, dengan kode_matkul
     * dan semester yang sama, tetapi kelas berbeda. Dipakai untuk mengisi pemilih
     * kelas-tujuan dan aksi copy/fan-out. Hasilnya di-memoize per instance sehingga
     * pemanggilan berulang dalam satu request tidak melakukan query ulang.
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
     * Kunci stabil untuk mengelompokkan course siblings pada tampilan (dashboard, sidebar).
     * Dikelompokkan berdasarkan nama_matkul agar course lama yang kode_matkul-nya berbeda
     * tetap mengelompok menjadi satu grup.
     */
    public function getCourseGroupKeyAttribute(): string
    {
        return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
    }
}
