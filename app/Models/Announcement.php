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
    ];

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
