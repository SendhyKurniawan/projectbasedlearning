<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model step (tahapan) sebuah tugas ber-step. Mahasiswa mengerjakan step secara
// berurutan: step N terbuka setelah step N-1 dikumpulkan (lihat Assignment::currentStepFor).
class AssignmentStep extends Model
{
    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_id',
        'step_number',
        'title',
        'description',
        'deadline',
        'submission_format', // null = ikut format tugas induk
        'max_score',         // bobot nilai step (dipakai saat mode per_step)
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    // Tugas induk step ini.
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Pengumpulan mahasiswa untuk step ini.
    public function submissions()
    {
        return $this->hasMany(StepSubmission::class);
    }

    // Format pengumpulan efektif: milik step sendiri, atau ikut tugas induk.
    public function effectiveSubmissionFormat(): string
    {
        return $this->submission_format ?: ($this->assignment->submission_format ?? 'pdf');
    }

    // Cek apakah mahasiswa tertentu sudah mengumpulkan step ini.
    public function isSubmittedBy($mahasiswaId): bool
    {
        return $this->submissions()->where('mahasiswa_id', $mahasiswaId)->exists();
    }
}
