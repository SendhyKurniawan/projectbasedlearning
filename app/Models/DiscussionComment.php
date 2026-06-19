<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDiscussionComment
 */
// Model komentar/balasan pada sebuah topik Discussion.
class DiscussionComment extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'discussion_id',
        'user_id',
        'content',
    ];

    // Topik diskusi tempat komentar ini berada.
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    // Penulis komentar.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
