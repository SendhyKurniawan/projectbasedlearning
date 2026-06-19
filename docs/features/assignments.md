# Tugas (Assignments)

## Tiga tipe

`assignments.type` adalah enum dengan nilai `tugas | quiz | exercise`. Tipe menggerakkan alur pengumpulan, field mana yang berlaku, dan cara penilaian bekerja.

| Tipe | Mahasiswa mengumpulkan | Auto-grade | Jalur nilai |
|---|---|---|---|
| `tugas` | Berkas PDF atau URL | Tidak | Dosen menilai via `Dosen\AssignmentController::grade` (atau `gradeGroup` untuk tugas kelompok) |
| `quiz` | Form kuis (berwaktu bila `duration_minutes` diset) | MC saja | Kuis MC murni dinilai otomatis saat submit; kuis dengan soal `essay`/`code_snippet` mendapat nilai MC sementara, dosen mereview via `showQuizAttempt` |
| `exercise` | Kode via CodeMirror | Tidak (hanya petunjuk) | Dosen menilai via `grade`; petunjuk keyword-match masuk ke `submissions.validation_result` |

Daftar kolom assignment lengkap ada di [database.md](../database.md#assignments). Field penting:

- `order` — urutan drag-and-drop dalam mata kuliah (semua tipe)
- `assignment_number` — nomor tampilan berurutan ("Tugas/Latihan ke-N", untuk `tugas` dan `exercise`)
- `quiz_number` — nomor tampilan berurutan ("Kuis ke-N", quiz saja)
- `submission_format` — `pdf|url` (tugas saja; untuk non-tugas, saat store dipaksa `pdf`)
- `is_group`, `max_group_size`, `grading_mode` — pengaturan tugas kelompok
- `duration_minutes` — batas waktu kuis; null = tak terbatas
- `required_material_id` — gerbang prasyarat; bila diset, mahasiswa harus punya `MaterialView` untuk materi itu
- `exercise_config` — array dicast JSON, payload khusus exercise (`language`, `starter_code`, `solution_code`, `required_keywords[]`, `hints[]`)

---

## Route penulisan oleh dosen

### Generik (tugas + quiz)

```
GET    /dosen/courses/{course}/assignments                 assignments.index
GET    /dosen/courses/{course}/assignments/create          assignments.create
POST   /dosen/courses/{course}/assignments                 assignments.store
POST   /dosen/courses/{course}/assignments/reorder         assignments.reorder
GET    /dosen/assignments/{assignment}/edit                assignments.edit
PUT    /dosen/assignments/{assignment}                     assignments.update
DELETE /dosen/assignments/{assignment}                     assignments.destroy
POST   /dosen/assignments/{assignment}/copy                assignments.copy
GET    /dosen/assignments/{assignment}/submissions         assignments.submissions
POST   /dosen/submissions/{submission}/grade               submissions.grade
POST   /dosen/groups/{group}/grade                         groups.grade
```

### Manajemen soal kuis

```
GET    /dosen/assignments/{assignment}/questions               assignments.questions.index
GET    /dosen/assignments/{assignment}/questions/create        assignments.questions.create
POST   /dosen/assignments/{assignment}/questions               assignments.questions.store
GET    /dosen/questions/{question}/edit                        assignments.questions.edit
PUT    /dosen/questions/{question}                             assignments.questions.update
DELETE /dosen/questions/{question}                             assignments.questions.destroy
GET    /dosen/assignments/{assignment}/submissions/{submission} assignments.submissions.show
```

### Exercise (khusus, terpisah dari create generik)

```
GET    /dosen/courses/{course}/exercises/create  exercises.create
POST   /dosen/courses/{course}/exercises         exercises.store
GET    /dosen/exercises/{assignment}/edit        exercises.edit
PUT    /dosen/exercises/{assignment}             exercises.update
```

CRUD exercise punya alur sendiri karena form-nya didominasi editor starter/solution CodeMirror + input `required_keywords` dipisah-koma dan `hints` dipisah-baris.

Semua route penulisan digerbang `AssignmentPolicy` atau `CoursePolicy::update` — lihat [auth-roles.md](../auth-roles.md).

---

## Tugas

### Field

- `submission_format`: `pdf` atau `url` — menggerakkan input form pengumpulan mahasiswa
- `is_group` (boolean) — mengaktifkan pembentukan kelompok
- `max_group_size` (integer, termasuk pengirim) — batas ukuran kelompok
- `grading_mode`: `equal` (satu nilai untuk semua anggota) atau `individual` (input per-anggota)
- `assignment_number`: nomor tampilan berurutan

### Create / update

Validasi di `Dosen\AssignmentController::store`:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'deadline' => 'required|date|after:now',
    'max_score' => 'required|integer|min:1|max:100',
    'type' => 'required|in:tugas,quiz,exercise',
    'has_duration' => 'nullable|boolean',
    'submission_format' => 'nullable|in:pdf,url',
    'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
    'is_group' => 'nullable|boolean',
    'max_group_size' => 'nullable|integer|min:2|max:20',
    'grading_mode' => 'nullable|in:equal,individual',
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

Setelah create, controller men-dispatch `AcademicUpdateNotification` ke semua mahasiswa terdaftar mata kuliah (dan tiap kelas sibling bila fan-out dipilih). Pengaturan kelompok **terkunci begitu ada submission** — `update()` mengabaikan `is_group`, `max_group_size`, `grading_mode` bila `$assignment->submissions()->exists()`.

`assignment_number` dihitung per tipe per mata kuliah: hitung baris yang ada bertipe sama, +1. Hal yang sama berlaku untuk `quiz_number` pada tipe quiz.

### Penilaian

`POST /dosen/submissions/{submission}/grade` (tugas mahasiswa-tunggal):

```php
$submission->update([
    'score' => $request->score,
    'feedback' => $request->feedback,
    'status' => 'graded',
]);
// lalu: Notification::send($student, new GradeNotification($assignment->title, $course->id));
```

`POST /dosen/groups/{group}/grade` untuk tugas kelompok:

- `grading_mode = equal` — satu `$request->score` ditulis ke setiap submission dalam kelompok
- `grading_mode = individual` — `$request->scores[mahasiswa_id]` dan `$request->feedbacks[mahasiswa_id]` per anggota, hanya anggota yang `score`-nya diset yang mendapat `status=graded`

Kedua alur mengirim `GradeNotification` ke anggota yang dinilai.

### Quick-grade (buku nilai)

`PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade` — edit satu sel inline pada view buku nilai (`Dosen\GradeController::quickGrade`). Memakai `updateOrCreate` sehingga bisa membuat baris submission seketika untuk mahasiswa yang tak pernah mengumpulkan:

```php
Submission::updateOrCreate(
    ['assignment_id' => $assignment->id, 'mahasiswa_id' => $mahasiswa->id, 'group_id' => null],
    ['score' => $data['score'], 'feedback' => $data['feedback'] ?? null, 'status' => 'graded']
);
```

`submitted_at` diset pada penilaian pertama bila kosong agar baris tidak permanen ber-timestamp null.

---

## Quiz

### Tipe soal (`quiz_questions.question_type`)

| Tipe | Dinilai otomatis |
|---|---|
| `pilihan_ganda` | Ya — `QuizController::submit` menambah `score_weight` tiap soal yang `answers[question_id]` yang dikirim cocok dengan opsi ber-`is_correct=true`. |
| `essay` | Tidak — dosen mereview. |
| `code_snippet` | Tidak — dosen mereview. |

Opsi hanya ada untuk `pilihan_ganda`. `score_weight` per soal default 1.

### Alur mahasiswa

```
GET  /mahasiswa/assignments/{assignment}/quiz          quizzes.show
POST /mahasiswa/assignments/{assignment}/quiz/start    quizzes.start
GET  /mahasiswa/assignments/{assignment}/quiz/take     quizzes.take
POST /mahasiswa/assignments/{assignment}/quiz/submit   quizzes.submit
GET  /mahasiswa/assignments/{assignment}/quiz/result   quizzes.result
```

1. `show` — halaman info. Bila submission punya `finished_at`, redirect langsung ke `result`.
2. `start` — `Submission::firstOrCreate({assignment, mahasiswa}, {started_at: now()})`. Redirect ke `take`.
3. `take` — render form jawaban. Bila sudah selesai, redirect kembali ke `show`.
4. `submit` — nilai soal MC dalam transaksi DB, tulis `answers` JSON, set `finished_at = now()`, set `score` ke subtotal MC.
   - **Semua soal pilihan_ganda** → `status = graded` (final).
   - **Ada essay atau code_snippet** → `status = submitted` (nilai MC sementara, menunggu dosen).
5. `result` — render nilai, opsional dengan pengungkapan jawaban benar.

Penilaian MC adalah satu query per submit — kumpulkan id opsi yang dikirim, ambil baris ber-`is_correct=true`, lalu iterasi koleksi soal menambah `score_weight` untuk tiap kecocokan.

### Review percobaan kuis oleh dosen

`GET /dosen/assignments/{assignment}/submissions` untuk kuis dirutekan ke view `dosen.assignments.quiz_attempts` (`Dosen\AssignmentController::submissions`). Tiap baris menautkan ke:

`GET /dosen/assignments/{assignment}/submissions/{submission}` (`assignments.submissions.show`) yang merender `dosen.assignments.quiz_attempt_show` — per-soal dengan jawaban benar ditandai. Dosen lalu bisa ke `submissions.grade` untuk menetapkan nilai akhir pada kuis essay/code_snippet.

### Perilaku copy kuis

Menyalin kuis membuat assignment **cangkang** tanpa soal. Pesan sukses memberi tahu dosen untuk menambah soal secara terpisah. Ini disengaja: set soal sering perlu penyesuaian per kelas.

---

## Exercise

### Struktur JSON `exercise_config`

```json
{
  "language": "java",
  "starter_code": "public class Main { ... }",
  "solution_code": "public class Main { ... }",
  "required_keywords": ["public", "static", "void", "main"],
  "hints": ["Print to stdout", "Use System.out.println"]
}
```

Dicast di `Assignment::casts()` sebagai `'exercise_config' => 'array'`. Akses via `$assignment->exercise_config['key']`. Form `Dosen\ExerciseController` mem-post:

- `exercise_language` → dipetakan ke `language`
- `required_keywords` → string dipisah-koma → array via `array_map('trim', explode(',', …))`
- `hints` → string dipisah-baris → array

Nilai `exercise_language` yang diizinkan: `html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`. Hanya `java`, `php`, `csharp` yang bisa mem-proxy ke Piston via `/execute-code` (lihat `config/code_execution.php` dan [architecture.md](../architecture.md#sandbox-eksekusi-kode-piston)). Yang HTML/CSS/JS berjalan di iframe preview sisi klien.

### Alur mahasiswa

```
GET  /mahasiswa/exercises/{assignment}/solve    exercises.solve  (check.assignment.unlocked)
POST /mahasiswa/exercises/submit                exercises.submit
```

1. `solve` — render `mahasiswa.exercises.solve` dengan editor CodeMirror terisi dari `starter_code`. Submission yang ada (bila ada) dimuat untuk ditampilkan.
2. Tombol "Run" → `POST /execute-code` (`CodeExecutionController`, dibatasi 10/menit). Mengembalikan `{stdout, stderr, exit_code}`.
3. "Submit" → `POST /mahasiswa/exercises/submit`. Controller memvalidasi `code_answer`, menjalankan `validateCode()` (keyword match), dan menyimpan semuanya:

```php
Submission::create([
    'assignment_id' => $assignment->id,
    'mahasiswa_id' => $mahasiswa->id,
    'code_answer' => $request->code_answer,
    'validation_result' => $validationResult,
    'auto_graded' => false,
    'score' => null,
    'status' => 'submitted',
    'submitted_at' => now(),
]);
```

Bentuk `validation_result`:

```json
{
  "passed": false,
  "score": 60,
  "feedback": "Missing required element: return\nFound 3/5 required elements.",
  "validated_at": "2026-05-28 10:30:00"
}
```

Ini **petunjuk yang ditampilkan ke dosen** sebagai "Validasi Mesin" di view pengumpulan (`resources/views/dosen/assignments/submissions.blade.php`). Ia **tidak** menetapkan `score` atau mengubah `status`. Tidak ada kolom `auto_grade` — desain WIP sebelumnya pernah ada; sudah di-revert.

Dosen menilai exercise secara manual via `Dosen\AssignmentController::grade` → `GradeNotification`.

---

## Prasyarat materi

Tugas apa pun dapat mewajibkan mahasiswa membuka materi tertentu dulu:

```php
$assignment->required_material_id;          // FK ke materials, nullable
$assignment->isUnlockedFor($userId);        // bool
$assignment->requiredMaterial;              // belongsTo(Material)
```

`isUnlockedFor()` mengembalikan `true` bila `required_material_id` null, jika tidak mengecek baris `MaterialView`. Middleware `CheckAssignmentUnlocked` menerapkan ini pada:

```php
Route::resource('submissions', Mahasiswa\SubmissionController::class)
    ->except(['index', 'show'])
    ->middleware('check.assignment.unlocked');

Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])
    ->middleware('check.assignment.unlocked');
```

Kegagalan me-redirect kembali dengan flash `error`. Lihat [auth-roles.md](../auth-roles.md#middleware-checkassignmentunlocked).

---

## Field urutan tampilan — kenapa tiga?

| Field | Dipakai untuk |
|---|---|
| `order` | Posisi drag-and-drop dalam mata kuliah (semua tipe). Diperbarui oleh `assignments.reorder`. |
| `assignment_number` | Label "Tugas ke-N" atau "Latihan ke-N", hanya dipakai di view tugas/exercise. |
| `quiz_number` | Label "Kuis ke-N", hanya dipakai di view kuis. |

Field nomor diset saat create sebagai `count(tipe-cocok-dalam-course) + 1` dan hanya untuk tampilan — tidak diperbarui saat reorder. `order` adalah kunci sortir kanonik.

---

## Copy fan-out

`POST /dosen/assignments/{assignment}/copy` menyalin assignment ke kelas sibling terpilih. Irisan keamanan sama seperti di tempat lain — lihat [contributing.md](../contributing.md).

Field yang disalin: `title`, `description`, `deadline`, `max_score`, `type`, `submission_format`, `duration_minutes`, `is_group`, `max_group_size`, `grading_mode`, `exercise_config`.

Per salinan:

- `course_id` = sibling
- `assignment_number` = `(jumlah assignment bertipe sama di sibling) + 1`
- `quiz_number` = logika sama untuk kuis
- `order` = `(sibling.assignments.max('order') ?? 0) + 1`

Bila `type === 'quiz'`, pesan sukses mengingatkan dosen untuk menambah soal di tiap sibling secara terpisah.

---

## Notifikasi

Setiap mutasi yang memengaruhi visibilitas mahasiswa men-dispatch `AcademicUpdateNotification`:

| Aksi | Penerima | Judul |
|---|---|---|
| `store` (non-quiz) | mahasiswa terdaftar `$course` | "Tugas/Exercise Baru Ditambahkan" |
| `store` fan-out per sibling | mahasiswa terdaftar sibling | sama |
| `update` | mahasiswa terdaftar `$course` | "Tugas/Exercise Diperbarui" |
| `grade` | mahasiswa tersebut | `GradeNotification` |
| `gradeGroup` | semua anggota yang dinilai | `GradeNotification` |

Lihat [notifications.md](notifications.md) untuk semantik channel/queue.
