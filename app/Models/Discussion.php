<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin IdeHelperDiscussion
 */
// Model topik diskusi (forum). Tiap topik dibuat seorang user dan punya banyak komentar.
class Discussion extends Model
{
    // Setiap kali diskusi dibuat/diubah/dihapus, kosongkan cache daftar diskusi di sidebar.
    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('discussions:index:sidebar');
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'topic',
    ];

    // Pembuat topik diskusi.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Komentar/balasan pada topik ini.
    public function comments()
    {
        return $this->hasMany(DiscussionComment::class);
    }
}
