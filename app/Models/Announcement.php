<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model pengumuman. target_audience menentukan sasaran: all/dosen/mahasiswa/specific.
// Bisa menyertakan satu lampiran (gambar atau PDF).
class Announcement extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'target_audience',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
    ];

    // Apakah pengumuman punya lampiran.
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    // Apakah lampirannya berupa gambar (untuk ditampilkan inline).
    public function attachmentIsImage(): bool
    {
        return $this->hasAttachment() && str_starts_with((string) $this->attachment_mime, 'image/');
    }

    // Apakah lampirannya berupa PDF.
    public function attachmentIsPdf(): bool
    {
        return $this->hasAttachment() && $this->attachment_mime === 'application/pdf';
    }

    /**
     * User pembuat pengumuman.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User-user spesifik yang menjadi sasaran (saat target_audience = 'specific').
     */
    public function targetedUsers()
    {
        return $this->belongsToMany(User::class, 'announcement_user');
    }
}
