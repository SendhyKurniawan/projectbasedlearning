# Pengumpulan (Submissions)

## Daur hidup

```
                  (submission baru)
                        │
                        ▼
                  ┌─────────────┐
                  │  submitted  │
                  └──────┬──────┘
                         │  dosen menilai
                         ▼
                  ┌─────────────┐
                  │   graded    │
                  └─────────────┘

(submit kuis, semua-pilihan-ganda)
                        │
                        ▼
                  ┌─────────────┐
                  │   graded    │   (status=graded diset inline; nilai MC final)
                  └─────────────┘

(submit kuis, campuran/essay/code_snippet)
                        │
                        ▼
                  ┌─────────────┐
                  │  submitted  │   (nilai MC sementara; dosen mereview)
                  └─────────────┘
```

Status `late` ada pada enum (kolom `status` adalah `enum('submitted','late','graded')`) tetapi **belum ada controller yang menyetelnya**. Handler pengumpulan membuat baris dengan default `submitted` dan hanya `quickGrade`/`grade`/`gradeGroup` yang membaliknya ke `graded`. Bila Anda perlu menandai pengumpulan terlambat, tambahkan cek saat submit:

```php
'status' => now()->greaterThan($assignment->deadline) ? 'late' : 'submitted',
```

---

## Pengumpulan tugas

### Route mahasiswa (`Mahasiswa\SubmissionController`)

Resource didaftarkan dengan `->except(['index', 'show'])` dan middleware `check.assignment.unlocked`:

```
GET    /mahasiswa/submissions/create        submissions.create
POST   /mahasiswa/submissions               submissions.store
GET    /mahasiswa/submissions/{submission}/edit  submissions.edit
PUT    /mahasiswa/submissions/{submission}   submissions.update
DELETE /mahasiswa/submissions/{submission}   submissions.destroy
```

### `create` (GET)

Membaca `?assignment_id=` dari query string, memuat assignment + mata kuliah, memvalidasi enrollment, lalu:

- Bila assignment `is_group` dan user sudah dalam kelompok untuk assignment ini, muat kelompok dengan anggota + pembuat.
- Bila `is_group` dan belum dalam kelompok, muat `classmates` — mahasiswa terdaftar yang belum berada dalam kelompok untuk assignment ini. Merender picker agar pengirim dapat membentuk kelompok saat submit.

Merender `mahasiswa.submissions.create` dengan `assignment`, submission `existing` (bila ada), `existingGroup`, `classmates`.

### `store` (POST)

Validasi bergantung pada `submission_format`:

```php
$rules = [
    'assignment_id' => 'required|exists:assignments,id',
    'notes' => 'nullable|string',
];

if ($assignment->submission_format === 'url') {
    $rules['url_link'] = 'required|url|max:2048';
} else {
    $rules['file'] = 'required|file|max:10240';   // batas 10 MB
}

if ($assignment->is_group) {
    $rules['member_ids'] = 'required|array|min:1';
    $rules['member_ids.*'] = 'integer|exists:users,id';
    $rules['group_name'] = 'nullable|string|max:120';
}
```

Lalu:

1. Cek ulang enrollment via `enrollments()->where('courses.id', ...)`.
2. Batalkan dengan flash error bila submission untuk user+assignment ini sudah ada.
3. Simpan berkas (path: `submissions/{time()}_{user_id}_{name}`) pada disk `public` atau set `url_link`.
4. **Bila kelompok**: validasi tiap anggota terpilih terdaftar di mata kuliah DAN belum jadi anggota kelompok lain untuk assignment ini DAN ukuran kelompok (termasuk pengirim) ≤ `max_group_size`. Bungkus dalam transaksi:
   - Buat `Group` dengan `created_by_mahasiswa_id = pengirim`.
   - Untuk tiap anggota (termasuk pengirim): buat `GroupMember` + baris `Submission` dengan `file_path`/`url_link`/`notes` yang sama.
   - Kirim `AcademicUpdateNotification` ke anggota LAIN ("Ditambahkan ke Kelompok").
5. **Bila individu**: satu baris `Submission`.
6. Kirim `SubmissionNotification` ke dosen mata kuliah.

Pengumpulan kelompok dicerminkan ke beberapa baris agar penilaian terlihat pada setiap anggota, sambil tetap membuat view tiap anggota menampilkan baris submission-nya sendiri.

### `edit`, `update`

Blokir bila `score !== null` (sudah dinilai). Untuk pengumpulan kelompok, hanya `created_by_mahasiswa_id` (pembuat kelompok) yang dapat mengedit.

Saat update, berkas atau URL diganti. Untuk pengumpulan kelompok, update cermin berlaku ke **setiap baris dalam kelompok**:

```php
if ($submission->group_id) {
    Submission::where('group_id', $submission->group_id)->update($data);
}
```

### `destroy`

Penggerbangan sama dengan `edit` + `update`. Untuk kelompok: hanya pembuat yang dapat menghapus, dan penghapusan kaskade ke semua baris `Submission` + baris `GroupMember` + `Group` itu sendiri. Berkas (bila ada) dilepas tautannya dulu.

---

## Pengumpulan kuis

Ditangani oleh `Mahasiswa\QuizController`, bukan `SubmissionController`. Lihat [assignments.md#quiz](assignments.md#quiz) untuk daftar route lengkap dan perilakunya.

Semantik pengumpulan kunci:

- `start` membuat `Submission` via `firstOrCreate(['assignment_id','mahasiswa_id'], ['started_at' => now()])`.
- `submit` menulis `answers` (peta JSON `{question_id: option_id_or_text}`), set `finished_at = now()`, set `score` ke subtotal MC, dan set `status`:
  - Semua soal `pilihan_ganda` → `status = graded`.
  - Ada `essay`/`code_snippet` → `status = submitted` (sementara).
- Mahasiswa hanya bisa menyelesaikan kuis sekali — cek `finished_at` di `take()` memblokir masuk ulang.

`auto_graded` diset `true` hanya untuk jalur MC-murni (dan hanya oleh kode mendatang; saat ini `submit()` tidak men-toggle kolom itu — verifikasi bila Anda bergantung padanya).

---

## Pengumpulan exercise

Ditangani oleh `Mahasiswa\ExerciseController::submit` (`POST /mahasiswa/exercises/submit`):

```php
$request->validate([
    'assignment_id' => 'required|exists:assignments,id',
    'code_answer' => 'required|string',
]);

// Verifikasi enrollment, tolak bila sudah submit.

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
```

Tombol "Run" di halaman solve menuju `POST /execute-code` secara terpisah dan **tidak** membuat submission. Hanya "Submit" eksplisit yang membuat baris.

### `validation_result`

Dihitung oleh `ExerciseController::validateCode($assignment, $code)`:

```php
foreach ($exercise_config['required_keywords'] as $keyword) {
    if (stripos($code, $keyword) !== false) {
        $foundKeywords++;
    } else {
        $passed = false;
        $feedback[] = "Missing required element: {$keyword}";
    }
}
$score = floor(($foundKeywords / $totalKeywords) * $assignment->max_score);

return [
    'passed' => $score === $maxScore,
    'score' => $score,
    'feedback' => implode("\n", $feedback),
    'validated_at' => now()->toDateTimeString(),
];
```

Hasil ditampilkan ke dosen sebagai "Validasi Mesin" di `resources/views/dosen/assignments/submissions.blade.php`. **Ia tidak menetapkan `score` atau `status`.** Dosen menetapkan nilai sesungguhnya melalui `submissions.grade`.

---

## Tugas kelompok

| Model | Tujuan |
|---|---|
| `Group` (tabel `groups`) | Satu per (assignment, tim). Punya `assignment_id`, `group_name`, `created_by_mahasiswa_id`. |
| `GroupMember` (tabel `group_members`) | Pivot: `group_id`, `mahasiswa_id`. Unik pada `(group_id, mahasiswa_id)`. |

`Group` menyediakan: `assignment()`, `members()`, `submissions()`, `creator()` (user `created_by_mahasiswa_id`), `hasMember($userId)`.

Hanya `created_by_mahasiswa_id` yang dapat mengedit/menghapus pengumpulan kelompok. Anggota dapat melihat tetapi tak bisa mengubah baris submission.

Penilaian dilakukan lewat `Dosen\AssignmentController::gradeGroup` (`POST /dosen/groups/{group}/grade`). Dua mode yang diset pada assignment:

- `grading_mode = equal` — `$request->score` dan `$request->feedback` ditulis ke setiap submission dalam kelompok (satu nilai bersama).
- `grading_mode = individual` — `$request->scores[mahasiswa_id]` dan `$request->feedbacks[mahasiswa_id]` per baris. Hanya anggota yang `score`-nya diset (`!== null && !== ''`) yang mendapat `status = graded`. Sisanya tetap `submitted`.

`GradeNotification` dikirim ke semua anggota yang punya baris submission.

---

## Notifikasi

| Peristiwa | Notifikasi | Penerima |
|---|---|---|
| Store tugas (individu atau kelompok) | `SubmissionNotification` | dosen mata kuliah |
| Store kelompok (anggota lain ditambahkan) | `AcademicUpdateNotification("Ditambahkan ke Kelompok", …)` | anggota kelompok lain |
| Nilai individu | `GradeNotification($assignment->title, $course->id)` | mahasiswa tersebut |
| Nilai kelompok | `GradeNotification(...)` | semua anggota dengan submission |

Lihat [notifications.md](notifications.md).

---

## View nilai mahasiswa

`Mahasiswa\GradeController::index` (`GET /mahasiswa/grades`) mendaftar semua pengumpulan mahasiswa dengan join assignment + mata kuliah. Dipakai di entri sidebar "Nilai".

`Dosen\GradeController` mengekspor rekap CSV per-kelas:

`GET /dosen/grades/{course}/export` → `Dosen\GradeController::export` mengalirkan CSV dengan `NIM`, `Nama`, satu kolom per assignment, dan rata-rata berjalan. Diotorisasi oleh `CoursePolicy::view`.
