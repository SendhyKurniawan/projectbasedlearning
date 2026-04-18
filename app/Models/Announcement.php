<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'target_audience',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
    ];

    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function attachmentIsImage(): bool
    {
        return $this->hasAttachment() && str_starts_with((string) $this->attachment_mime, 'image/');
    }

    public function attachmentIsPdf(): bool
    {
        return $this->hasAttachment() && $this->attachment_mime === 'application/pdf';
    }

    /**
     * Get the user that authored the announcement.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the specific users targeted by this announcement (if target_audience is 'specific').
     */
    public function targetedUsers()
    {
        return $this->belongsToMany(User::class, 'announcement_user');
    }
}
