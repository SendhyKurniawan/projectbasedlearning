<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGroup
 */
class Group extends Model
{
    protected $fillable = [
        'assignment_id',
        'group_name',
        'created_by_mahasiswa_id',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_mahasiswa_id');
    }

    public function hasMember($mahasiswaId): bool
    {
        return $this->members()->where('mahasiswa_id', $mahasiswaId)->exists();
    }
}
