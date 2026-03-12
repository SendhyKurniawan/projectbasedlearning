<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDiscussion
 */
class Discussion extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'topic',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(DiscussionComment::class);
    }
}
