<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model kelas/rombongan belajar (mis. "TI-3A"). Menampung mahasiswa dan menjadi acuan
// pembuatan course per-kelas.
class StudentClass extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'study_program_id',
        'semester_id',
        'name',
    ];

    // Prodi tempat kelas ini berada.
    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    // Semester aktif kelas ini.
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Mahasiswa anggota kelas.
    public function students()
    {
        return $this->hasMany(User::class, 'student_class_id');
    }

    // Matkul (course) yang dialokasikan ke kelas ini.
    public function courses()
    {
        return $this->hasMany(Course::class, 'student_class_id');
    }
}
