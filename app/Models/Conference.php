<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
// Model sesi konferensi video (Jitsi self-hosted) milik sebuah matkul.
// room_name dipakai sebagai nama ruangan Jitsi; status: scheduled/live/ended.
class Conference extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
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

    // Casting kolom jadwal & waktu berakhir menjadi datetime.
    protected $casts = [
        'scheduled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Matkul tempat konferensi berlangsung.
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Dosen penyelenggara konferensi.
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    // Apakah konferensi sedang berlangsung.
    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    // Apakah konferensi sudah selesai.
    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }
}
