<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model pengumpulan mahasiswa untuk satu step tugas. Untuk tugas kelompok dibuat
// satu baris per anggota dengan group_id sama (pola sama dengan Submission).
class StepSubmission extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_step_id',
        'mahasiswa_id',
        'group_id',
        'file_path',
        'url_link',
        'notes',
        'submitted_at',
        'score',
        'feedback',
        'status', // submitted, late, graded
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    // Step yang dikumpulkan.
    public function step()
    {
        return $this->belongsTo(AssignmentStep::class, 'assignment_step_id');
    }

    // Mahasiswa pemilik pengumpulan.
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    // Kelompok pengumpul (untuk tugas kelompok).
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
