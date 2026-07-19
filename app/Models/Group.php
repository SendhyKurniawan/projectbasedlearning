<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGroup
 */
// Model kelompok mahasiswa untuk tugas kelompok. Tiap kelompok terikat ke satu Assignment
// dan dibuat oleh seorang mahasiswa (created_by_mahasiswa_id).
class Group extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_id',
        'group_name',
        'created_by_mahasiswa_id',
    ];

    // Tugas yang dikerjakan kelompok ini.
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Anggota kelompok.
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    // Pengumpulan yang dibuat atas nama kelompok.
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // Pengumpulan step yang dibuat atas nama kelompok (tugas ber-step).
    public function stepSubmissions()
    {
        return $this->hasMany(StepSubmission::class);
    }

    // Mahasiswa pembuat/ketua kelompok.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_mahasiswa_id');
    }

    // Cek apakah seorang mahasiswa termasuk anggota kelompok ini.
    public function hasMember($mahasiswaId): bool
    {
        return $this->members()->where('mahasiswa_id', $mahasiswaId)->exists();
    }
}
