<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model program studi (prodi), berada di bawah sebuah jurusan; level mis. D3/D4/S1.
class StudyProgram extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'level',
    ];

    // Jurusan induk prodi ini.
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Kelas (rombongan belajar) milik prodi ini.
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }
}
