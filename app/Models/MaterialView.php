<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialView extends Model
{
    /**
     * Disable timestamps - we only use viewed_at
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'material_id',
        'mahasiswa_id',
        'viewed_at',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'viewed_at' => 'datetime',
    ];
    
    /**
     * Get the material that was viewed.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
    
    /**
     * Get the student who viewed the material.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
}
