<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model jurusan/departemen — tingkat tertinggi hierarki akademik
// (jurusan → program studi → kelas → matkul).
class Department extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'name',
        'code',
    ];

    // Program studi yang bernaung di bawah jurusan ini.
    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class);
    }
}
