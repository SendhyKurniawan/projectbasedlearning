<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperSemester
 */
// Model semester (mis. Ganjil/Genap) di dalam sebuah tahun ajaran.
// is_active menandai semester yang sedang berjalan.
class Semester extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    // Casting kolom tanggal & status aktif.
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Tahun ajaran induk semester ini.
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Matkul (course) yang berjalan pada semester ini.
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
