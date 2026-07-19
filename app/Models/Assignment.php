<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $deadline
 * @property int $max_score
 * @property string $type
 * @property array<array-key, mixed>|null $exercise_config
 * @property int|null $order
 * @property int|null $assignment_number
 * @property int|null $quiz_number
 * @property int|null $duration_minutes
 * @property string|null $submission_format
 * @property int|null $required_material_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submission> $submissions
 * @property-read int|null $submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuizQuestion> $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \App\Models\Material|null $requiredMaterial
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assignment query()
 * @method static \Database\Factories\AssignmentFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 * @mixin IdeHelperAssignment
 */
// Model tugas/penilaian. Kolom 'type' menentukan jenisnya: tugas, quiz, atau exercise.
// Bisa berupa tugas individu maupun kelompok (lihat kolom is_group).
class Assignment extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi massal.
    protected $fillable = [
        'assignment_number',
        'submission_format',
        'course_id',
        'title',
        'order',
        'description',
        'deadline',
        'max_score',
        'type', // tugas, quiz, exercise
        'exercise_config',
        'required_material_id',
        'duration_minutes',
        'quiz_number',
        'is_group',
        'max_group_size',
        'grading_mode',
        'step_grading_mode', // final = satu nilai akhir, per_step = akumulasi nilai step
    ];

    // Casting kolom: deadline jadi datetime, exercise_config disimpan sebagai JSON (array).
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'exercise_config' => 'array',
            'is_group' => 'boolean',
        ];
    }

    // Relasi
    // Matkul tempat tugas ini berada.
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    // Seluruh pengumpulan (submission) mahasiswa untuk tugas ini.
    public function submissions()
    {
        return $this->hasMany(\App\Models\Submission::class);
    }

    // Daftar soal (khusus tugas bertipe quiz).
    public function questions()
    {
        return $this->hasMany(\App\Models\QuizQuestion::class);
    }

    // Kelompok mahasiswa (khusus tugas kelompok).
    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }

    // Materi prasyarat: tugas baru terbuka setelah materi ini dibaca mahasiswa.
    public function requiredMaterial()
    {
        return $this->belongsTo(Material::class, 'required_material_id');
    }

    // Step (tahapan) tugas ber-step, urut sesuai step_number.
    public function steps()
    {
        return $this->hasMany(AssignmentStep::class)->orderBy('step_number');
    }

    // Method bantu

    /**
     * Cek apakah tugas ini sudah "terbuka" (boleh diakses) untuk seorang mahasiswa.
     * Bernilai true jika tidak ada materi prasyarat, atau jika mahasiswa sudah pernah
     * tercatat membuka materi prasyarat tersebut.
     */
    public function isUnlockedFor($studentId): bool
    {
        if (! $this->required_material_id) {
            return true;
        }

        return MaterialView::where('material_id', $this->required_material_id)
            ->where('student_id', $studentId)
            ->exists();
    }

    // Cek apakah tugas ini punya step progresi.
    public function hasSteps(): bool
    {
        return $this->steps()->exists();
    }

    /**
     * Step aktif untuk seorang mahasiswa: step pertama (urutan terkecil) yang belum
     * ia kumpulkan. Null berarti semua step sudah selesai (atau tidak ada step).
     */
    public function currentStepFor($mahasiswaId): ?AssignmentStep
    {
        return $this->steps()
            ->whereDoesntHave('submissions', fn ($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->orderBy('step_number')
            ->first();
    }

    // Semua step sudah dikumpulkan oleh mahasiswa ini (gate submission final).
    public function allStepsCompletedBy($mahasiswaId): bool
    {
        return $this->currentStepFor($mahasiswaId) === null;
    }

    // Jumlah step yang sudah dikumpulkan mahasiswa ini.
    public function stepsCompletedCountFor($mahasiswaId): int
    {
        return StepSubmission::whereIn('assignment_step_id', $this->steps()->pluck('id'))
            ->where('mahasiswa_id', $mahasiswaId)
            ->count();
    }

    // Scope query
    // Tugas yang masih aktif (deadline belum lewat).
    public function scopeActive($query)
    {
        return $query->where('deadline', '>=', now());
    }

    // Tugas yang sudah lewat deadline.
    public function scopePast($query)
    {
        return $query->where('deadline', '<', now());
    }
}
