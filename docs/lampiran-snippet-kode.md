# Lampiran — Cuplikan Kode (Source Code Snippets)

Dokumen ini memuat cuplikan kode inti dari **Platform Pembelajaran Berbasis Proyek (Project-Based Learning)** yang dibangun menggunakan **Laravel 12** dengan tampilan *server-rendered* Blade, Tailwind CSS, dan Alpine.js.

Setiap cuplikan disertai keterangan lokasi berkas, nomor baris, dan penjelasan singkat fungsinya. Kode-kode di bawah ini dipilih karena mewakili konsep arsitektur, keamanan, dan logika bisnis utama sistem.

**Daftar Cuplikan:**

1. [Model Mata Kuliah & Konsep *Siblings* Multi-Kelas](#a-1)
2. [Middleware Penjaga Peran (Role-Based Access Control)](#a-2)
3. [Policy Otorisasi Mata Kuliah](#a-3)
4. [Service Pembuatan Token JWT Konferensi (Jitsi)](#a-4)
5. [Proxy Eksekusi Kode (Code Execution)](#a-5)
6. [Verifikasi OTP saat Registrasi](#a-6)
7. [Penilaian Kuis Otomatis (Auto-Grading)](#a-7)
8. [Pengumpulan & Validasi Latihan Koding](#a-8)
9. [Penyalinan Tugas ke Kelas Paralel (Fan-out)](#a-9)

---

<a id="a-1"></a>

## A.1 Model Mata Kuliah & Konsep *Siblings* Multi-Kelas

**Berkas:** `app/Models/Course.php`
**Penjelasan:** Satu baris `Course` merepresentasikan satu kelas dari sebuah mata kuliah. Seorang dosen yang mengajar mata kuliah yang sama ke beberapa kelas akan memiliki beberapa baris `Course` — baris-baris ini disebut *siblings*. Method `siblings()` mengambil kelas-kelas paralel tersebut (di-*memoize* agar tidak query berulang), dan `booted()` otomatis mengosongkan cache sidebar saat data berubah.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

// Model mata kuliah (course). Satu baris Course = satu kelas dari sebuah matkul.
// Dosen yang mengajar matkul sama (kode_matkul + semester) ke beberapa kelas akan
// punya beberapa baris Course; baris-baris itu disebut "siblings".
class Course extends Model
{
    use HasFactory;

    // Saat course dibuat/diubah/dihapus, kosongkan cache sidebar milik dosen terkait
    // agar daftar matkul pada sidebar selalu sinkron dengan data terbaru.
    protected static function booted(): void
    {
        $flush = fn (self $course) => Cache::forget("sidebar:dosen:{$course->dosen_id}");
        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    // Kolom yang boleh diisi secara massal (mass assignment).
    protected $fillable = [
        'nama_matkul', 'kode_matkul', 'description', 'dosen_id', 'sks',
        'semester_id', 'student_class_id', 'course_img',
    ];

    // Dosen pengampu matkul ini.
    public function dosen()
    {
        return $this->belongsTo(\App\Models\User::class, 'dosen_id');
    }

    // Mahasiswa yang terdaftar (enroll) di matkul ini, via tabel pivot enrollments.
    public function students()
    {
        return $this->belongsToMany(\App\Models\User::class, 'enrollments', 'course_id', 'mahasiswa_id')
            ->withPivot('final_grade', 'enrolled_at')
            ->withTimestamps();
    }

    // Cache siblings per instance agar tidak query berulang dalam satu request.
    protected ?\Illuminate\Support\Collection $cachedSiblings = null;

    /**
     * Course "siblings": matkul lain yang diampu dosen yang sama, dengan kode_matkul
     * dan semester yang sama, tetapi kelas berbeda. Dipakai untuk mengisi pemilih
     * kelas-tujuan dan aksi copy/fan-out. Hasilnya di-memoize per instance.
     */
    public function siblings(): \Illuminate\Support\Collection
    {
        return $this->cachedSiblings ??= static::where('dosen_id', $this->dosen_id)
            ->where('kode_matkul', $this->kode_matkul)
            ->where('semester_id', $this->semester_id)
            ->where('id', '!=', $this->id)
            ->with('studentClass')
            ->orderBy('student_class_id')
            ->get();
    }

    /**
     * Kunci stabil untuk mengelompokkan course siblings pada tampilan (dashboard, sidebar).
     */
    public function getCourseGroupKeyAttribute(): string
    {
        return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
    }
}
```

> *Catatan: anotasi `@property` dan beberapa relasi sekunder (`semester`, `studentClass`, `materials`, `conferences`) dihilangkan dari cuplikan demi keringkasan. Lihat berkas asli untuk versi lengkap.*

---

<a id="a-2"></a>

## A.2 Middleware Penjaga Peran (Role-Based Access Control)

**Berkas:** `app/Http/Middleware/CheckRole.php`
**Penjelasan:** Middleware ini dipasang pada grup rute sebagai `role:admin`, `role:dosen`, atau `role:mahasiswa`. Ia memastikan hanya pengguna dengan peran sesuai yang dapat mengakses rute tersebut; jika tidak cocok, pengguna dipantulkan ke dashboard sesuai perannya sendiri.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware penjaga peran (dipakai sebagai `role:admin|dosen|mahasiswa` pada grup route).
// Memastikan user yang login berperan sesuai; jika tidak, dipantulkan ke dashboard-nya sendiri.
class CheckRole
{
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        // Belum login → arahkan ke halaman login.
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'dosen', 'mahasiswa']) || $user->role !== $requiredRole) {
            // Peran tidak cocok → pantulkan ke dashboard sesuai peran user.
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'dosen' => redirect()->route('dosen.dashboard'),
                'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return $next($request);
    }
}
```

---

<a id="a-3"></a>

## A.3 Policy Otorisasi Mata Kuliah

**Berkas:** `app/Policies/CoursePolicy.php`
**Penjelasan:** Otorisasi tingkat objek menggunakan mekanisme *Policy* Laravel (`$this->authorize(...)`). Pola umumnya: **admin boleh segalanya**, sedangkan **dosen hanya boleh mengakses mata kuliah miliknya**. Ini mencegah seorang dosen mengubah data mata kuliah dosen lain.

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

// Policy hak akses mata kuliah. Pola umum: admin boleh semua; dosen hanya untuk matkul miliknya.
class CoursePolicy
{
    // Boleh melihat daftar matkul: admin atau dosen.
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    // Boleh melihat satu matkul: admin, atau dosen pengampu matkul tersebut.
    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh membuat: admin selalu boleh; dosen boleh (cek matkul bila konteksnya ada).
    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // $course null berarti pengecekan create umum; izinkan dosen mana pun.
        if ($user->isDosen()) {
            return $course === null || $course->dosen_id === $user->id;
        }

        return false;
    }

    // Boleh mengubah: admin, atau dosen pengampu matkul tersebut.
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh menghapus: hanya admin.
    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
```

---

<a id="a-4"></a>

## A.4 Service Pembuatan Token JWT Konferensi (Jitsi)

**Berkas:** `app/Services/JitsiTokenService.php`
**Penjelasan:** Fitur konferensi video menggunakan **Jitsi self-hosted**. Setiap pengguna yang masuk ruang konferensi diberi **JWT bertanda tangan HS256** dengan masa berlaku 2 jam. Status moderator (dosen/admin `true`, mahasiswa `false`) dibawa dalam klaim `context.user.moderator`, sehingga hak moderator ditentukan oleh token, bukan oleh siapa yang masuk lebih dulu.

```php
<?php

namespace App\Services;

use Firebase\JWT\JWT;
use RuntimeException;

// Service pembuat token & URL ruangan untuk Jitsi self-hosted.
// Token ditandatangani HS256 dengan secret yang harus sama dengan JWT_APP_SECRET di server Jitsi.
class JitsiTokenService
{
    // Buat (mint) JWT HS256 untuk satu user pada satu ruangan. Berlaku 2 jam.
    // Flag moderator dibawa di context.user.moderator (dosen/admin true, mahasiswa false).
    public function mint(string $room, int $userId, string $name, bool $moderator, ?string $email = null): string
    {
        $appId = config('services.jitsi.jwt_app_id');
        $secret = config('services.jitsi.jwt_app_secret');
        $domain = config('services.jitsi.domain');

        // Tanpa kredensial JWT, konferensi tidak bisa berjalan — hentikan dengan error jelas.
        if (!$appId || !$secret) {
            throw new RuntimeException('Jitsi JWT credentials not configured (JITSI_JWT_APP_ID / JITSI_JWT_APP_SECRET).');
        }

        $now = time();
        $payload = [
            'aud' => $appId,        // aud = iss = jwt_app_id
            'iss' => $appId,
            'sub' => $domain,       // sub = domain server Jitsi
            'room' => $room,
            'iat' => $now,
            'nbf' => $now - 10,     // beri toleransi 10 detik untuk selisih jam
            'exp' => $now + 7200,   // masa berlaku token 2 jam
            'context' => [
                'user' => [
                    'id' => (string) $userId,
                    'name' => $name,
                    'avatar' => '',
                    'email' => $email ?? '',
                    'moderator' => $moderator ? 'true' : 'false',
                ],
            ],
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}
```

> *Catatan: method `roomUrl()` (penyusun URL ruangan + override branding) dihilangkan demi keringkasan.*

---

<a id="a-5"></a>

## A.5 Proxy Eksekusi Kode (Code Execution)

**Berkas:** `app/Http/Controllers/CodeExecutionController.php`
**Penjelasan:** Mahasiswa dapat menjalankan kode dari editor di browser. Demi keamanan, frontend **tidak memanggil mesin eksekusi langsung**, melainkan melalui proxy server ini yang meneruskan ke **Piston API**. Bahasa pemrograman dibatasi *allowlist*, ukuran kode dibatasi, rute di-*throttle* 10 permintaan/menit, dan setiap kegagalan ditangani secara eksplisit.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Controller proxy eksekusi kode: meneruskan kode dari klien ke Piston API di sisi server
// (jangan dipanggil langsung dari frontend). Di-throttle 10/menit oleh route.
class CodeExecutionController extends Controller
{
    // Jalankan kode lewat Piston. Bahasa dibatasi allowlist & dipetakan ke nama bahasa Piston.
    public function execute(Request $request): JsonResponse
    {
        $map = config('code_execution.piston_language_map', []);

        $validated = $request->validate([
            'code' => 'required|string|max:50000',
            'language' => 'required|string|in:' . implode(',', array_keys($map)),
        ]);

        $pistonLanguage = $map[$validated['language']];
        $base = rtrim((string) config('services.piston.url'), '/');
        $timeout = (int) config('services.piston.timeout', 10);

        try {
            $response = Http::timeout($timeout)->acceptJson()->post($base . '/execute', [
                'language' => $pistonLanguage,
                'version'  => '*',
                'files'    => [['content' => $validated['code']]],
            ]);
        } catch (ConnectionException | RequestException $e) {
            // Gagal menghubungi Piston → kembalikan respons "layanan tidak tersedia".
            Log::warning('Piston request failed', ['error' => $e->getMessage()]);
            return $this->serviceUnavailable();
        }

        if (!$response->successful()) {
            Log::warning('Piston returned non-2xx', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return $this->serviceUnavailable();
        }

        $run = $response->json('run', []);

        return response()->json([
            'stdout'    => (string) ($run['stdout'] ?? ''),
            'stderr'    => (string) ($run['stderr'] ?? ''),
            'exit_code' => (int) ($run['code'] ?? -1),
        ]);
    }

    // Respons standar saat layanan eksekusi (Piston) tidak dapat diakses (HTTP 502).
    private function serviceUnavailable(): JsonResponse
    {
        return response()->json([
            'stdout'    => '',
            'stderr'    => 'Execution service unavailable.',
            'exit_code' => -1,
        ], 502);
    }
}
```

---

<a id="a-6"></a>

## A.6 Verifikasi OTP saat Registrasi

**Berkas:** `app/Http/Controllers/Auth/OtpVerificationController.php`
**Penjelasan:** Saat registrasi, pengguna harus memverifikasi email lewat **kode OTP 6 digit** (berlaku 10 menit). Pembandingan kode memakai `hash_equals()` untuk mencegah *timing attack*. Setelah verifikasi: **mahasiswa langsung aktif**, sedangkan **dosen menunggu persetujuan admin** sebelum akunnya dapat digunakan.

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// Controller verifikasi OTP saat registrasi. User pending diidentifikasi lewat sesi
// (otp_user_id), bukan lewat login, karena akunnya belum aktif.
class OtpVerificationController extends Controller
{
    // Verifikasi OTP, lalu aktifkan (mahasiswa) atau serahkan ke persetujuan admin (dosen).
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.digits' => 'Kode verifikasi harus 6 digit angka.',
        ]);

        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['code' => 'Sesi verifikasi tidak ditemukan. Silakan daftar ulang.']);
        }

        // Tolak jika kode tidak ada atau sudah kadaluarsa.
        if (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'Kode telah kadaluarsa. Silakan kirim ulang kode baru.',
            ]);
        }

        // Bandingkan kode secara aman (hash_equals) untuk cegah timing attack.
        if (!hash_equals((string) $user->otp_code, (string) $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid.',
            ]);
        }

        $isDosen = $user->role === 'dosen';

        $user->forceFill([
            'otp_verified_at'   => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            // Mahasiswa langsung aktif; dosen masih perlu persetujuan admin.
            'is_active'         => !$isDosen,
        ])->save();

        $request->session()->forget('otp_user_id');

        // Dosen: kembali ke login dengan pesan menunggu persetujuan admin.
        if ($isDosen) {
            return redirect()->route('login')
                ->with('status', 'Email berhasil diverifikasi. Akun dosen Anda sedang menunggu persetujuan admin sebelum dapat digunakan.');
        }

        // Mahasiswa: langsung login dan masuk dashboard.
        Auth::login($user);

        return redirect()->route('mahasiswa.dashboard');
    }

    // Kirim ulang kode OTP baru (berlaku 10 menit) ke email user pending.
    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('register');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        $user->notify(new OtpVerificationNotification($code));

        return back()->with('status', 'Kode verifikasi baru telah dikirim ke ' . $user->email . '.');
    }

    // Ambil user pending dari id yang disimpan di sesi (tanpa autentikasi).
    protected function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('otp_user_id');
        if (!$id) {
            return null;
        }

        return User::find($id);
    }
}
```

> *Catatan: method `create()` (menampilkan halaman input OTP) dihilangkan demi keringkasan.*

---

<a id="a-7"></a>

## A.7 Penilaian Kuis Otomatis (Auto-Grading)

**Berkas:** `app/Http/Controllers/Mahasiswa/QuizController.php`
**Penjelasan:** Method `submit()` menilai jawaban kuis di dalam satu **transaksi basis data**. Soal **pilihan ganda dinilai otomatis**. Status hasil ditetapkan `graded` (final) hanya bila tidak ada soal esai/kode; jika ada, status `submitted` dengan skor sementara yang menunggu penilaian dosen.

```php
// Kumpulkan jawaban quiz: nilai pilihan ganda otomatis di dalam satu transaksi DB.
public function submit(Request $request, Assignment $assignment)
{
    $submission = Submission::where('assignment_id', $assignment->id)
        ->where('mahasiswa_id', auth()->id())
        ->firstOrFail();

    if ($submission->finished_at) {
         return redirect()->route('mahasiswa.quizzes.show', $assignment);
    }

    $activeQuestions = $assignment->questions;

    $hasNonAutogradable = $activeQuestions
        ->whereIn('question_type', ['essay', 'code_snippet'])
        ->isNotEmpty();

    DB::transaction(function () use ($request, $submission, $activeQuestions, $hasNonAutogradable) {
        $answers = $request->input('answers', []);
        $calculatedScore = 0;

        $mcQuestionIds = $activeQuestions->where('question_type', 'pilihan_ganda')->pluck('id')->toArray();
        $submittedOptionIds = [];
        foreach ($mcQuestionIds as $qId) {
            if (isset($answers[$qId]) && is_numeric($answers[$qId])) {
                $submittedOptionIds[] = $answers[$qId];
            }
        }

        // Ambil hanya opsi yang dijawab DAN bertanda benar (is_correct).
        $correctOptions = collect();
        if (!empty($submittedOptionIds)) {
            $correctOptions = QuizOption::whereIn('id', $submittedOptionIds)
                ->where('is_correct', true)
                ->get()
                ->keyBy('id');
        }

        foreach ($activeQuestions as $question) {
            $userAnswer = $answers[$question->id] ?? null;

            if ($question->question_type === 'pilihan_ganda') {
                if ($userAnswer && isset($correctOptions[$userAnswer])) {
                    $calculatedScore += $question->score_weight;
                }
            }
        }

        // Skor hanya mencakup pilihan ganda; dianggap final hanya bila tidak ada soal essay/code_snippet.
        $submission->update([
            'finished_at' => now(),
            'score' => $calculatedScore,
            'answers' => $answers,
            'status' => $hasNonAutogradable ? 'submitted' : 'graded',
        ]);
    });

    $flash = $hasNonAutogradable
        ? 'Kuis berhasil dikumpulkan! Skor sementara dari pilihan ganda; soal essay/kode menunggu penilaian dosen.'
        : 'Kuis berhasil dikumpulkan! Skor final telah tersimpan.';

    return redirect()->route('mahasiswa.quizzes.result', $assignment)
        ->with('success', $flash);
}
```

> *Catatan: method pendukung (`show`, `start`, `take`, `result`) dihilangkan; hanya logika inti penilaian yang ditampilkan.*

---

<a id="a-8"></a>

## A.8 Pengumpulan & Validasi Latihan Koding

**Berkas:** `app/Http/Controllers/Mahasiswa/ExerciseController.php`
**Penjelasan:** Untuk latihan koding (*exercise*), sistem **tidak menilai otomatis**. Method `validateCode()` mencocokkan kode terhadap *required_keywords* dan menghasilkan ringkasan yang disimpan sebagai **petunjuk (hint)** bagi dosen — skor resmi tetap `null` dan diberikan dosen secara manual.

```php
public function submit(Request $request)
{
    $request->validate([
        'assignment_id' => 'required|exists:assignments,id',
        'code_answer' => 'required|string',
    ]);

    $mahasiswa = auth()->user();
    $assignment = Assignment::findOrFail($request->assignment_id);

    $isEnrolled = DB::table('enrollments')
        ->where('mahasiswa_id', $mahasiswa->id)
        ->where('course_id', $assignment->course_id)
        ->exists();
    if (!$isEnrolled) {
        return redirect()->route('mahasiswa.dashboard')
            ->with('error', 'Anda tidak terdaftar di course ini.');
    }

    $existing = Submission::where('assignment_id', $assignment->id)
        ->where('mahasiswa_id', $mahasiswa->id)
        ->first();

    if ($existing) {
        return redirect()->back()->with('error', 'Anda sudah mengumpulkan exercise ini.');
    }

    // Validasi keyword dijalankan sebagai petunjuk (hint) untuk dosen, bukan untuk penilaian.
    $validationResult = $this->validateCode($assignment, $request->code_answer);

    Submission::create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id' => $mahasiswa->id,
        'code_answer' => $request->code_answer,
        'validation_result' => $validationResult,
        'auto_graded' => false,
        'score' => null,
        'feedback' => null,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    return redirect()->route('mahasiswa.courses.show', $assignment->course_id)
        ->with('success', 'Code berhasil dikumpulkan! Menunggu penilaian dosen.');
}

// Hitung kecocokan kode terhadap required_keywords di exercise_config; hasilnya berupa
// ringkasan (lolos/skor/umpan balik) yang disimpan sebagai hint, BUKAN nilai resmi.
private function validateCode($assignment, $code)
{
    $config = $assignment->exercise_config;
    $maxScore = $assignment->max_score;
    $score = 0;
    $feedback = [];

    if (isset($config['required_keywords']) && !empty($config['required_keywords'])) {
        $totalKeywords = count($config['required_keywords']);
        $foundKeywords = 0;

        foreach ($config['required_keywords'] as $keyword) {
            if (stripos($code, $keyword) !== false) {
                $foundKeywords++;
            } else {
                $feedback[] = "Missing required element: {$keyword}";
            }
        }

        $score = floor(($foundKeywords / $totalKeywords) * $maxScore);

        if ($foundKeywords === $totalKeywords) {
            $feedback[] = "All required elements found.";
        } else {
            $feedback[] = "Found {$foundKeywords}/{$totalKeywords} required elements.";
        }
    } else {
        $score = $maxScore;
        $feedback[] = "Code submitted successfully.";
    }

    return [
        'passed' => $score === $maxScore,
        'score' => $score,
        'feedback' => implode("\n", $feedback),
        'validated_at' => now()->toDateTimeString(),
    ];
}
```

---

<a id="a-9"></a>

## A.9 Penyalinan Tugas ke Kelas Paralel (Fan-out)

**Berkas:** `app/Http/Controllers/Dosen/AssignmentController.php`
**Penjelasan:** Dosen dapat menyalin satu tugas ke beberapa kelas paralel (*siblings*) sekaligus. Secara keamanan, daftar kelas tujuan yang dikirim dari form **di-*intersect*** dengan daftar *siblings* milik mata kuliah tersebut (`$course->siblings()`), sehingga dosen tidak dapat menargetkan mata kuliah milik dosen lain dengan memalsukan request.

```php
public function copy(Request $request, Assignment $assignment)
{
    $assignment->loadMissing('course');
    $course = $assignment->course;
    $this->authorize('update', $course);

    $request->validate([
        'sibling_ids' => 'required|array|min:1',
        'sibling_ids.*' => 'integer|exists:courses,id',
    ]);

    // Batasi target ke siblings milik matkul ini saja (cegah penargetan matkul dosen lain).
    $allowedSiblingIds = $course->siblings()->pluck('id');
    $targetIds = collect($request->sibling_ids)
        ->map(fn($id) => (int) $id)
        ->intersect($allowedSiblingIds);

    if ($targetIds->isEmpty()) {
        return back()->with('error', 'Pilih kelas tujuan yang valid.');
    }

    $sharedData = $assignment->only([
        'title', 'description', 'deadline', 'max_score', 'type',
        'submission_format', 'duration_minutes', 'is_group',
        'max_group_size', 'grading_mode', 'exercise_config',
    ]);

    $targetCourses = Course::whereIn('id', $targetIds)->get();
    foreach ($targetCourses as $sibling) {
        $count    = Assignment::where('course_id', $sibling->id)->where('type', $sharedData['type'])->count();
        $maxOrder = $sibling->assignments()->max('order') ?? 0;
        Assignment::create(array_merge($sharedData, [
            'course_id'         => $sibling->id,
            'quiz_number'       => $sharedData['type'] === 'quiz'  ? $count + 1 : null,
            'assignment_number' => $sharedData['type'] !== 'quiz'  ? $count + 1 : null,
            'order'             => $maxOrder + 1,
        ]));
    }

    $msg = "'{$assignment->title}' disalin ke {$targetIds->count()} kelas lain.";
    if ($assignment->type === 'quiz') {
        $msg .= ' Tambahkan pertanyaan secara terpisah di kelas tujuan.';
    }

    return back()->with('success', $msg);
}
```

---

*Seluruh cuplikan di atas merupakan kode asli dari sistem. Komentar berbahasa Indonesia merupakan bagian dari kode sumber dan tidak ditambahkan khusus untuk lampiran ini.*
