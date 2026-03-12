<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $course_id
 * @property int $dosen_id
 * @property string $title
 * @property string? $description
 * @property string $room_name
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property \Illuminate\Support\Carbon? $ended_at
 * @property string $status
 * @property-read \App\Models\Course $course
 * @property-read \App\Models\User $dosen
 */
class Conference extends Model
{
    protected $fillable = [
        'course_id',
        'dosen_id',
        'title',
        'description',
        'room_name',
        'scheduled_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }
}
