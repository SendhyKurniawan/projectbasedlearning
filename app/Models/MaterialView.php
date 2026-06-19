<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMaterialView
 */
// Model pencatat "materi sudah dibaca": satu baris = satu mahasiswa membuka satu materi.
// Dipakai sebagai syarat membuka tugas (lihat Assignment::isUnlockedFor).
class MaterialView extends Model
{
    /**
     * Nonaktifkan timestamps bawaan; kita hanya memakai kolom viewed_at.
     */
    public $timestamps = false;

    /**
     * Kolom yang boleh diisi massal.
     */
    protected $fillable = [
        'material_id',
        'student_id',
        'viewed_at',
    ];

    /**
     * Casting kolom waktu baca menjadi datetime.
     */
    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Materi yang dibuka.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Mahasiswa yang membuka materi.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
