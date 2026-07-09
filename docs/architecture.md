# Arsitektur

## Apa aplikasi ini

**PBL Workspace** adalah LMS Laravel 12 (server-rendered) untuk Project-Based Learning. Setiap halaman adalah Blade dengan sentuhan Tailwind + Alpine. Tepat ada satu komponen Livewire (`App\Livewire\Discussion\Show`). Tidak ada SPA, Inertia, maupun Pusher/Reverb. Istilah domain berbahasa Indonesia (`mata_kuliah`, `kode_matkul`, `sks`, `nim`, `nip`, `dosen`, `mahasiswa`) — itu disengaja, jangan di-Inggriskan.

---

## Sistem tiga role

Setiap user memiliki tepat satu nilai di `users.role` (enum MySQL):

| Nilai | Prefix route | Route dashboard | Middleware |
|---|---|---|---|
| `admin` | `/admin` | `admin.dashboard` | `auth` + `role:admin` |
| `dosen` | `/dosen` | `dosen.dashboard` | `auth` + `role:dosen` |
| `mahasiswa` | `/mahasiswa` | `mahasiswa.dashboard` | `auth` + `role:mahasiswa` |

Route root `/` memeriksa `auth()->user()->role` dan mengarahkan ke dashboard yang sesuai. Tamu dikirim ke `/login`. Tidak ada landing page bersama.

```
                    ┌─────────────────┐
                    │   /             │
                    │ redirect oleh   │
                    │ users.role      │
                    └────────┬────────┘
             ┌───────────────┼───────────────┐
             ▼               ▼               ▼
    /admin/dashboard  /dosen/dashboard  /mahasiswa/dashboard
```

**Admin** mengelola hierarki akademik (tahun, semester, jurusan, program studi, kelas), seluruh user, dan record mata kuliah global. Admin tidak menulis konten pembelajaran.

**Dosen** memiliki mata kuliah (satu baris per kelas) dan menulis konten pembelajaran — materi, tugas (tugas/quiz/exercise), konferensi. Dosen menilai pengumpulan.

**Mahasiswa** terdaftar di mata kuliah via pivot `enrollments`. Mahasiswa melihat konten, mengumpulkan tugas, mengerjakan kuis, bergabung konferensi saat sesi live.

### Route bersama auth-only (role apa pun)

- `GET /profile` / `PATCH /profile` / `DELETE /profile` — controller profil Breeze
- `GET /notifications`, `POST /notifications/mark-all-read`, `POST /notifications/{id}/mark-read`, `GET /notifications/{id}/redirect` — `NotificationController`
- `POST /execute-code` — `CodeExecutionController` (dibatasi `throttle:10,1`)
- `POST /push-subscribe`, `POST /push-unsubscribe` — `PushSubscriptionController`
- `Route::resource('discussions', ...)` — CRUD diskusi
- `Route::resource('announcements', ...)` — CRUD pengumuman

Lihat [auth-roles.md](auth-roles.md) untuk internal middleware dan detail policy.

---

## Hierarki akademik

```
AcademicYear ──┐
               │
               └── Semester ────┐
                                │
Department ──┐                  │
             │                  │
             └── StudyProgram ──┴── StudentClass ──┐
                                                   │
User (role=mahasiswa).student_class_id ────────────┤
                                                   │
Course (dosen_id, semester_id, student_class_id) ──┘
```

- `academic_years` (`year_start`, `year_end`, `is_active`)
- `semesters` (`academic_year_id`, `name` = "Ganjil"/"Genap", `start_date`, `end_date`, `is_active`)
- `departments` (`name`, `code` unik)
- `study_programs` (`department_id`, `name`, `code` unik, `level` = D3/D4/S1/S2/S3)
- `student_classes` (`study_program_id`, `semester_id`, `name`) — satu angkatan dalam satu semester
- `courses` — satu baris per tuple (dosen, matkul, kelas, semester)

Admin memelihara seluruh pohon di satu halaman terpadu `/admin/akademik` (`Admin\AkademikController`), dengan sub-aksi create/update/delete/activate di tiap level. Route CRUD per-level lama (`/admin/academic-years`, `/admin/departments`, dst.) masih terdaftar via `Route::resource()` demi kompatibilitas mundur, tetapi halaman terpadu adalah UI utama. URL `/admin/hierarchy/*` yang usang me-redirect ke `/admin/akademik`.

---

## Mata kuliah sibling dan `course_group_key`

Dosen yang mengajar `kode_matkul` sama ke beberapa kelas di semester yang sama mendapat satu baris `Course` per kelas. Baris-baris ini disebut **siblings** — sama `dosen_id + kode_matkul + semester_id`, berbeda `student_class_id`.

```php
// Course::siblings() — tidak termasuk diri sendiri, dimemo per-request via $cachedSiblings
$siblings = $course->siblings();  // Illuminate\Support\Collection<Course>

// Kunci pengelompokan stabil yang dipakai sidebar / dashboard
$key = $course->course_group_key;
// "{dosen_id}|{nama_matkul}|{semester_id}"  ← sengaja memakai nama_matkul, bukan kode_matkul
```

Mengapa `nama_matkul` dan bukan `kode_matkul` pada group key? Mata kuliah lama yang kodenya diganti tetap berkelompok dengan benar. Lihat `Course::getCourseGroupKeyAttribute()` (app/Models/Course.php:121).

### Cache sidebar

`SidebarComposer` (didaftarkan di `AppServiceProvider::boot()` untuk view `layouts.sidebar`) meng-cache daftar mata kuliah dosen di bawah `Cache::remember("sidebar:dosen:{$dosenId}", 300, ...)`. Hook `booted()` pada model `Course` membersihkan kunci tersebut setiap create/update/delete:

```php
// app/Models/Course.php
protected static function booted(): void
{
    $flush = fn (self $course) => Cache::forget("sidebar:dosen:{$course->dosen_id}");
    static::created($flush);
    static::updated($flush);
    static::deleted($flush);
}
```

Bila sidebar tidak menyegarkan setelah perubahan mata kuliah, periksa bahwa `dosen_id` pada mata kuliah cocok dengan user yang sedang login.

### Pencarian sidebar mahasiswa

Untuk mahasiswa, composer melakukan satu query mentah guna memilih mata kuliah terdaftar pertama (dipakai sebagai tautan lompat "ke mata kuliah saya"):

```php
$mahasiswaFirstCourse = DB::table('enrollments')
    ->where('mahasiswa_id', $user->id)
    ->value('course_id');
```

---

## Copy fan-out (Material / Assignment / Conference / Exercise)

Aksi dosen yang mempublikasikan konten hadir dalam dua bentuk:

1. **Create dengan fan-out** — form create menyertakan `@include('dosen.partials.sibling-kelas-picker')` yang merender checkbox untuk kelas sibling. `store()` membuat record utama, lalu mengulang `sibling_ids` tervalidasi untuk meng-fan-out salinan. Dipakai di: Material, Assignment, Conference, Exercise.
2. **Copy setelah create** — `<x-copy-modal>` terbuka dari view record yang ada dan mem-POST `sibling_ids` ke endpoint `copy` khusus. Dipakai di: `materials.copy`, `assignments.copy`, `conferences.copy`.

Kedua jalur menerapkan irisan keamanan yang sama di sisi server:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn ($id) => (int) $id)
    ->intersect($allowedSiblingIds);
// hanya iterasi $targetIds — jangan pernah input mentah
```

Tanpa irisan ini, POST yang dibuat-buat bisa menargetkan mata kuliah dosen lain. Lihat [contributing.md](contributing.md) untuk aturannya.

**Copy kuis** membuat cangkang — tidak ada soal yang disalin. Pesan sukses memberi tahu dosen untuk menambahkan soal ke tiap salinan secara terpisah (soal sering perlu penyesuaian per kelas).

**Copy berkas materi** menggandakan secara fisik berkas unggahan dengan akhiran unik (`_kelas{id}` pada create-fanout, `_kelas{id}_{time()}` pada copy berikutnya) sehingga menghapus materi satu sibling tidak melepas tautan berkas yang dipakai sibling lain.

---

## Alur request

```
Browser
  → Caddy di host VM (HTTPS, Let's Encrypt)
  → Nginx (alpine, kontainer) — menyajikan aset /build/* dengan cache panjang,
    meneruskan request aplikasi ke PHP-FPM via FastCGI pada app:9000
  → router Laravel → tumpukan middleware → controller
    → Eloquent / DB → Blade → response
  → opsional: queue worker mengambil notifikasi ShouldQueue (driver DB) atau berjalan inline (driver sync)
```

Tidak ada lapisan WebSocket. `BROADCAST_CONNECTION=log` menulis event broadcast ke berkas log dan tidak lebih — jangan panggil `broadcast()` dari kode baru. Push notification adalah satu-satunya channel pengiriman real-time, dikirim atas inisiatif server via VAPID WebPush.

---

## Sandbox eksekusi kode (Piston)

Tombol "Run" mahasiswa di halaman pengerjaan exercise dan tombol preview dosen mem-proxy kode lewat `POST /execute-code` (dibatasi 10/menit, perlu auth). `CodeExecutionController::execute()` memvalidasi bahasa terhadap `config('code_execution.piston_language_map')` — saat ini `java`, `php`, `csharp` (dengan C# dirutekan ke runtime `csharp.net` milik Piston). Lalu ia mem-POST ke `${PISTON_URL}/execute` dengan kode pengguna dan mengembalikan `{stdout, stderr, exit_code}`. Error koneksi / non-2xx mengembalikan HTTP 502 dengan `Execution service unavailable.`

Docker compose membundel `ghcr.io/engineer-man/piston` sebagai service `piston` di jaringan internal; `.env.example` mengarahkan `PISTON_URL` ke `http://piston:2000/api/v2`. Fallback tingkat config adalah `https://emkc.org/api/v2/piston` publik.

Bahasa editor exercise di sisi dosen mencakup lebih banyak (`html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`); yang HTML/CSS/JS dirender di iframe preview sisi klien alih-alih melewati Piston.

---

## Konvensi kunci sekilas

| Keputusan | Aturan |
|---|---|
| Validasi | `$request->validate([...])` inline di controller. Satu-satunya FormRequest adalah `LoginRequest` dan `ProfileUpdateRequest` (Breeze). |
| Otorisasi | `$this->authorize('verb', $model)` via Policy — tanpa Gate, tanpa cek role `abort(403)` inline. |
| Query pivot | Controller mahasiswa memakai query mentah `DB::table('enrollments')->where(...)` demi kecepatan. Pengecualian: `ScheduleController`, `SubmissionController`, `Mahasiswa\DashboardController` memakai relasi belongsToMany `User::enrollments()`. |
| Livewire | Satu komponen nyata: `App\Livewire\Discussion\Show`. Sisanya Blade biasa. |
| Field boolean form | Validasi sebagai `'nullable\|boolean'` dan cast di model. Form mengirim string `"1"`/`"0"`; aturan `boolean` menerima itu plus boolean asli. |
| Keamanan fan-out | Selalu iriskan `sibling_ids` yang dikirim dengan `$course->siblings()->pluck('id')`. |
| Lazy-loading | `Model::preventLazyLoading(!app()->isProduction())` diset di `AppServiceProvider::boot()` — lazy load N+1 melempar error di local/staging, dicatat diam-diam di produksi. |

---

## Peta fitur

- [courses.md](features/courses.md) — model Course, siblings, enrollment, copy fan-out
- [materials.md](features/materials.md) — penulisan konten, penanganan berkas, `MaterialView`, gerbang prasyarat
- [assignments.md](features/assignments.md) — tipe tugas / quiz / exercise, alur penilaian, tipe soal
- [submissions.md](features/submissions.md) — daur hidup pengumpulan, pengumpulan kelompok, petunjuk validasi
- [conferences.md](features/conferences.md) — Jitsi self-hosted JWT HS256, daur hidup ruang, peluncur tab baru
- [notifications.md](features/notifications.md) — channel database + WebPush, queueing
- [discussions.md](features/discussions.md) — satu-satunya komponen Livewire
- [schedule.md](features/schedule.md) — tampilan mendatang mahasiswa di `/mahasiswa/jadwal`
- SEO/metadata — `SeoController` (robots.txt/sitemap.xml dinamis) + `layouts/partials/seo.blade.php` (title/OG/Twitter/JSON-LD); lihat [frontend.md](frontend.md#seo--metadata)

Operasional:

- [ops/jitsi-self-host.md](ops/jitsi-self-host.md) — VM GCP, Caddy, stack Jitsi self-hosted
- [deployment.md](deployment.md) — checklist produksi, variabel env, queue, mail
- [getting-started.md](getting-started.md) — dev lokal, referensi env, akun hasil seed
- [testing.md](testing.md) — Pest, scaffolding Dusk, Playwright WIP
- [contributing.md](contributing.md) — konvensi kode, batas keamanan
- [auth-roles.md](auth-roles.md) — middleware, policy, alur OTP/registrasi
- [database.md](database.md) — referensi skema, gotcha
- [frontend.md](frontend.md) — entry point Vite, layout, pola Alpine, design system
