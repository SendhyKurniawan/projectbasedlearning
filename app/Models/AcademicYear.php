<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperAcademicYear
 */
// Model tahun ajaran (mis. 2025/2026). Menaungi beberapa semester; is_active menandai
// tahun ajaran yang sedang berjalan.
class AcademicYear extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'year_start',
        'year_end',
        'is_active',
    ];

    // is_active disimpan sebagai boolean.
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Semester yang berada dalam tahun ajaran ini.
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
