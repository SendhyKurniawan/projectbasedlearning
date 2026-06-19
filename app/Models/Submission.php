<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $student_id
 * @property string|null $file_path
 * @property string|null $notes
 * @property string|null $code_answer
 * @property array<array-key, mixed>|null $validation_result
 * @property bool $auto_graded
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property int|null $score
 * @property string|null $feedback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Assignment $assignment
 * @property-read bool $is_graded
 * @property-read \App\Models\User $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereAssignmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereAutoGraded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereCodeAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submission whereValidationResult($value)
 * @mixin \Eloquent
 * @mixin IdeHelperSubmission
 */
// Model pengumpulan jawaban mahasiswa atas sebuah Assignment. Menampung berkas, link,
// jawaban kode, jawaban quiz (JSON), nilai, dan feedback dari dosen.
class Submission extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_id',
        'mahasiswa_id',
        'group_id',
        'file_path',
        'url_link',
        'notes',
        'submitted_at',
        'score',
        'feedback',
        'status', // submitted, late, graded
        'code_answer',
        'validation_result',
        'auto_graded',
        'answers',
        'started_at',
        'finished_at',
    ];

    // Casting: kolom waktu jadi datetime; validation_result & answers disimpan sebagai JSON.
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'validation_result' => 'array',
            'answers' => 'array',
            'auto_graded' => 'boolean',
        ];
    }

    // Relasi
    // Tugas yang dikumpulkan.
    public function assignment()
    {
        return $this->belongsTo(\App\Models\Assignment::class);
    }

    // Mahasiswa pemilik pengumpulan.
    public function mahasiswa()
    {
        return $this->belongsTo(\App\Models\User::class, 'mahasiswa_id');
    }

    // Kelompok pengumpul (untuk tugas kelompok).
    public function group()
    {
        return $this->belongsTo(\App\Models\Group::class);
    }

    // Accessor
    // Dianggap sudah dinilai bila kolom score sudah terisi (bukan null).
    public function getIsGradedAttribute(): bool
    {
        return $this->score !== null;
    }
}
