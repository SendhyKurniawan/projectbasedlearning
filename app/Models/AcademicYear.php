<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperAcademicYear
 */
class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_start',
        'year_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
