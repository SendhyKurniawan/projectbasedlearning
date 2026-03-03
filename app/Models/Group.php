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
}
