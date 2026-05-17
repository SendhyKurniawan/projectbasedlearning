<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin IdeHelperDiscussion
 */
class Discussion extends Model
{
    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('discussions:index:sidebar');
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

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
