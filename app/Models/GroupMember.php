<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGroupMember
 */
// Model relasi keanggotaan: menghubungkan satu mahasiswa ke satu Group.
class GroupMember extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'group_id',
        'mahasiswa_id',
    ];

    // Kelompok tempat anggota ini berada.
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Mahasiswa yang menjadi anggota.
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
}
