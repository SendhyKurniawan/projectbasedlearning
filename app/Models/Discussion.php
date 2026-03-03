<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDiscussion
 */
class Discussion extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'content',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(DiscussionComment::class);
    }
}
