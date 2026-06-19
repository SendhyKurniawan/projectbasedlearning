# Basis Data

## Kelompok domain

```
Hierarki Akademik
  academic_years ── semesters ── student_classes ── users(role=mahasiswa)
                                       │
                                       └── (student_classes punya semester_id + study_program_id)
  departments ── study_programs ─────────┘

Orang
  users (enum role: admin | dosen | mahasiswa)
    - nim (unik nullable, mahasiswa)
    - nip (unik nullable, dosen)
    - student_class_id (kelas asal mahasiswa)
    - is_active, otp_*

Pembelajaran
  courses                     (dosen_id, semester_id, student_class_id)
    ├── materials             (order)
    │     └── material_views  (per mahasiswa, viewed_at)
    ├── assignments           (type: tugas|quiz|exercise, exercise_config JSON,
    │                          required_material_id, is_group, ...)
    │     ├── submissions     (per-mahasiswa, opsional group_id)
    │     ├── quiz_questions ── quiz_options
    │     └── groups ── group_members
    ├── conferences           (status: scheduled|live|ended)
    └── enrollments (pivot, mahasiswa_id)

Komunikasi
  discussions ── discussion_comments
  announcements ── announcement_user (pivot untuk target_audience='specific')
  notifications  (morph notifiable UUID Laravel)
  push_subscriptions (laravel-notification-channels/webpush)
```

`DB_CONNECTION` default di `.env.example` adalah `mysql` (selaras dengan service `db` di `docker-compose.yml`). SQLite dipakai pada test fitur `RefreshDatabase` milik Pest.

---

## Referensi tabel

### `users` (`0001_01_01_000000_create_users_table.php`, `2026_05_25_000000_add_otp_to_users_table.php`, `2026_03_08_063233_add_hierarchy_to_users_and_courses_table.php`, `2026_03_12_063456_add_indexes_to_core_tables.php`)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string | unik |
| `role` | enum | `mahasiswa\|dosen\|admin`, default `mahasiswa` |
| `nim` | string nullable | unik — NIM mahasiswa |
| `nip` | string nullable | unik — NIP dosen |
| `sso_id` | string nullable | dicadangkan untuk integrasi SSO mendatang; belum ada yang membacanya |
| `is_active` | boolean | default `true` (registrasi menimpa ke `false` sampai OTP/persetujuan admin) |
| `email_verified_at` | timestamp nullable | |
| `password` | string | bcrypt, `password` dicast sebagai `hashed` |
| `otp_code` | string(6) nullable | OTP 6 digit untuk verifikasi registrasi |
| `otp_expires_at` | timestamp nullable | 10 menit setelah diterbitkan |
| `otp_verified_at` | timestamp nullable | dosen mengisi ini lalu menunggu admin membalik `is_active` |
| `remember_token` | rememberToken | |
| `student_class_id` | foreignId nullable | kelas asal mahasiswa; null-on-delete |
| timestamps + indeks pada `role`, `is_active`, `student_class_id` | | |

Tabel pendamping: `password_reset_tokens(email PK, token, created_at)`, `sessions(id PK, user_id, ip_address, user_agent, payload, last_activity)`.

### `academic_years` / `semesters` / `departments` / `study_programs` / `student_classes`

| Tabel | Kolom |
|---|---|
| `academic_years` | `id`, `year_start`, `year_end`, `is_active`, timestamps |
| `semesters` | `id`, `academic_year_id` FK cascade, `name` ("Ganjil"/"Genap"), `start_date`, `end_date`, `is_active`, timestamps. Indeks pada `academic_year_id`. |
| `departments` | `id`, `name`, `code` unik, timestamps |
| `study_programs` | `id`, `department_id` FK cascade, `name`, `code` unik, `level` enum (`D3\|D4\|S1\|S2\|S3`, default `D4`), timestamps |
| `student_classes` | `id`, `study_program_id` FK cascade, `semester_id` FK cascade, `name`, timestamps. Indeks pada kedua FK. |

### `courses` (`2026_02_01_031051_create_courses_table.php`, `2026_03_08_063233_add_hierarchy_…`, `2026_05_14_000001_relax_course_kode_matkul_unique.php`, indeks)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `kode_matkul` | string | awalnya `unique`; unique tersebut **dihapus** oleh `relax_course_kode_matkul_unique` |
| `nama_matkul` | string | nama tampilan; dasar `course_group_key` |
| `sks` | integer | default `3` |
| `dosen_id` | foreignId users.id, cascade | |
| `semester_id` | foreignId nullable, null-on-delete | |
| `course_img` | string nullable | unggahan banner |
| `description` | text nullable | |
| `student_class_id` | foreignId nullable, null-on-delete | ditambahkan kemudian oleh `add_hierarchy_…` |
| timestamps + indeks pada `dosen_id`, `semester_id`, `student_class_id` | | |
| **Unik komposit** | `(kode_matkul, semester_id, student_class_id)` bernama `courses_code_semester_class_unique` | satu course per (kode + semester + kelas). Seorang dosen bisa mengajar kode sama ke kelas berbeda di semester yang sama. |

### `enrollments` (`2026_02_01_031103_create_enrollments_table.php`, indeks)

| Kolom | Catatan |
|---|---|
| `id` | bigint PK |
| `course_id` | FK courses, cascade |
| `mahasiswa_id` | FK users, cascade |
| `final_grade` | decimal(5,2) nullable |
| `enrolled_at` | timestamp default CURRENT |
| timestamps + indeks pada kedua FK | |
| **Unik** | `(course_id, mahasiswa_id)` — mencegah enrollment duplikat |

**Konvensi penting**: mayoritas controller mahasiswa mengquery enrollments via `DB::table('enrollments')->where(...)->exists()` alih-alih lewat relasi belongsToMany `User::enrollments()`. Pengecualiannya adalah `Mahasiswa\ScheduleController`, `Mahasiswa\SubmissionController`, dan `Mahasiswa\DashboardController` yang memakai relasi langsung. **Bila Anda mengganti nama kolom di tabel ini, grep kedua pola**:

```bash
grep -r "DB::table('enrollments'" app/
grep -r "enrollments()" app/
```

### `materials` (`2026_02_01_031054_create_materials_table.php`, indeks)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `course_id` | foreignId cascade | terindeks |
| `title` | string | |
| `content` | text nullable | disimpan sebagai Markdown oleh EasyMDE; dirender sisi klien via `markdown-renderer.js` |
| `file_path` | string nullable | path pada disk `public` di bawah `materials/…` |
| `order` | integer default 0 | reorder drag-and-drop via `materials/reorder` |
| timestamps | | |

### `material_views` (`2026_02_01_091419_create_material_views_table.php`)

| Kolom | Catatan |
|---|---|
| `id` | bigint PK |
| `material_id` | FK materials, cascade |
| `student_id` | FK users, cascade |
| `viewed_at` | timestamp |
| **Unik** | `(material_id, student_id)` — satu baris per mahasiswa per materi |
| Indeks | `student_id` |

`MaterialView::$timestamps = false` (hanya `viewed_at` yang dilacak). Model menyediakan relasi `mahasiswa()` sebagai alias `student_id`.

### `assignments` (`2026_02_01_031057_create_assignments_table.php`, `2026_03_19_231831_add_order_to_assignments_table.php`, `2026_05_04_120000_add_group_fields_to_assignments_and_groups.php`)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `course_id` | foreignId cascade | terindeks |
| `title` | string | |
| `order` | integer default 0 | reorder drag-and-drop |
| `assignment_number` | integer nullable | ditampilkan sebagai "Tugas ke-N" |
| `description` | text nullable | |
| `type` | enum | `tugas\|quiz\|exercise`, default `tugas` |
| `submission_format` | enum | `pdf\|url`, default `pdf` (hanya bermakna untuk tugas) |
| `exercise_config` | JSON nullable | dicast array — payload khusus exercise (lihat bawah) |
| `deadline` | datetime | |
| `max_score` | integer default 100 | |
| `required_material_id` | foreignId materials nullable, null-on-delete | gerbang prasyarat |
| `duration_minutes` | integer nullable | timer kuis; null = tak terbatas |
| `quiz_number` | integer nullable | ditampilkan sebagai "Kuis ke-N" |
| `is_group` | boolean default false | mode kelompok untuk tugas |
| `max_group_size` | unsignedInteger nullable | termasuk pengirim |
| `grading_mode` | enum | `equal\|individual`, default `equal` |
| timestamps | | |

**Struktur JSON `exercise_config`** (dicast array, tanpa kolom mandiri):

```json
{
  "language": "java",
  "starter_code": "public class Main { public static void main(String[] a) { } }",
  "solution_code": "public class Main { ... }",
  "required_keywords": ["public", "static", "void", "main"],
  "hints": ["Print to stdout", "Use System.out.println"]
}
```

Akses via `$assignment->exercise_config['language']`. Tidak ada kolom `auto_grade` — desain WIP sebelumnya pernah punya tapi sudah di-revert.

### `submissions` (`2026_02_01_031100_create_submissions_table.php`, `2026_03_01_000000_add_code_fields_to_submissions_table.php`, `2026_02_15_000000_create_quiz_and_group_tables.php` menambah `group_id`, indeks)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `assignment_id` | foreignId cascade | terindeks |
| `group_id` | foreignId groups nullable, null-on-delete | ditambahkan migrasi quiz_and_group |
| `mahasiswa_id` | foreignId users cascade | terindeks |
| `file_path` | string nullable | unggahan PDF tugas (`submissions/{time}_{user_id}_{name}`) |
| `url_link` | string nullable | pengumpulan URL tugas |
| `answers` | JSON nullable | peta jawaban kuis `{question_id: option_id_or_text}` |
| `notes` | text nullable | catatan mahasiswa |
| `submitted_at` | timestamp nullable | |
| `started_at` | timestamp nullable | mulai timer kuis |
| `finished_at` | timestamp nullable | waktu submit kuis |
| `score` | integer nullable | |
| `feedback` | text nullable | feedback dosen |
| `status` | enum | `submitted\|late\|graded`, default `submitted` |
| `code_answer` | text nullable | kode pengerjaan exercise |
| `validation_result` | JSON nullable | petunjuk validasi Piston/keyword (dicast array) |
| `auto_graded` | boolean default false | true hanya untuk kuis MC murni |
| timestamps | | |

**`validation_result`** adalah petunjuk, bukan nilai. Bentuk tersimpan dari `Mahasiswa\ExerciseController::validateCode()`:

```json
{
  "passed": false,
  "score": 60,
  "feedback": "Missing required element: return\nFound 3/5 required elements.",
  "validated_at": "2026-05-28 10:30:00"
}
```

Dosen melihatnya sebagai "Validasi Mesin" di view pengumpulan tetapi memberi nilai sesungguhnya secara manual. "passed/score" di dalam JSON hanya informatif — kolom `score` pada submission tetap `null` sampai dinilai.

### `groups` / `group_members` (`2026_02_15_000000_create_quiz_and_group_tables.php` + `2026_05_04_120000_add_group_fields_to_assignments_and_groups.php`)

| `groups` | |
|---|---|
| `id` | PK |
| `assignment_id` | FK assignments cascade |
| `group_name` | string |
| `created_by_mahasiswa_id` | FK users nullable, null-on-delete |
| timestamps | |

| `group_members` | |
|---|---|
| `id` | PK |
| `group_id` | FK groups cascade |
| `mahasiswa_id` | FK users cascade |
| timestamps | |
| **Unik** | `(group_id, mahasiswa_id)` bernama `group_members_group_mahasiswa_unique` |

Hanya `created_by_mahasiswa_id` yang dapat mengubah atau menghapus pengumpulan kelompok (`Mahasiswa\SubmissionController` memberlakukannya).

### `quiz_questions` / `quiz_options`

| `quiz_questions` | |
|---|---|
| `id` | PK |
| `assignment_id` | FK assignments cascade |
| `question_text` | text |
| `question_type` | enum (`essay\|pilihan_ganda\|code_snippet`) |
| `correct_answer` | text nullable |
| `score_weight` | integer default 1 |
| timestamps | |

| `quiz_options` | |
|---|---|
| `id` | PK |
| `question_id` | FK quiz_questions cascade |
| `option_text` | string |
| `is_correct` | boolean default false |
| timestamps | |

Opsi hanya diisi untuk soal `pilihan_ganda`. Handler submit menilai otomatis MC dengan menjumlahkan `score_weight` tiap soal yang opsi terpilihnya memiliki `is_correct=true`.

### `conferences` (`2026_03_01_000001_create_conferences_table.php`)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | PK | |
| `course_id` | FK courses cascade | |
| `dosen_id` | FK users cascade | yang membuat/memiliki sesi |
| `title` | string | |
| `description` | text nullable | |
| `room_name` | string **unik** | dibangkitkan sebagai `room-{course_id}-{uuid}`; dipakai sebagai identifier ruang Jitsi |
| `scheduled_at` | datetime | |
| `ended_at` | datetime nullable | |
| `status` | enum | `scheduled\|live\|ended`, default `scheduled` |
| timestamps | | |

Nilai status yang valid adalah **`scheduled`, `live`, `ended`** — tidak ada status `ongoing`, meskipun dokumen lama mungkin menyebut demikian.

### `discussions` / `discussion_comments`

`discussions` awalnya berskop course (FK `course_id`); migrasi `2026_03_12_065022_alter_discussions_table_replace_course_with_topic.php` menghapus FK itu dan menambah kolom teks bebas `topic`.

| `discussions` | |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — penulis |
| `topic` | string — mengkategorikan diskusi (teks bebas; topik populer diagregasi untuk sidebar) |
| `title` | string |
| `content` | text |
| timestamps | |

| `discussion_comments` | |
|---|---|
| `id` | PK |
| `discussion_id` | FK discussions cascade |
| `user_id` | FK users cascade |
| `content` | text — **bukan** `body` |
| timestamps | |

### `announcements` / `announcement_user` (`2026_03_12_065146_create_announcements_table.php`, `2026_03_12_065149_create_announcement_user_table.php`, `2026_04_18_000000_add_attachment_to_announcements_table.php`)

| `announcements` | |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — penulis |
| `title` | string |
| `content` | text |
| `target_audience` | enum (`all\|dosen\|mahasiswa\|specific`), default `all` |
| `attachment_path` / `attachment_name` / `attachment_mime` | ditambahkan kemudian untuk lampiran berkas |
| timestamps | |

| `announcement_user` | (pivot untuk `target_audience='specific'`) |
|---|---|
| `id` | PK |
| `announcement_id` | FK cascade |
| `user_id` | FK cascade |
| timestamps | |
| Unik | `(announcement_id, user_id)` |

### `notifications` (`2026_03_12_065659_create_notifications_table.php`)

Notifikasi channel-database standar Laravel.

| Kolom | Catatan |
|---|---|
| `id` | UUID PK |
| `type` | nama kelas notifikasi lengkap (FQN) |
| `notifiable_type` / `notifiable_id` | morph polimorfik |
| `data` | text (payload JSON-encoded dari `toArray()`) |
| `read_at` | timestamp nullable |
| timestamps | |

### `push_subscriptions` (`2026_03_19_235123_create_push_subscriptions_table.php`)

Disediakan oleh paket `laravel-notification-channels/webpush`; migrasi memakai `config('webpush.database_connection')` dan `config('webpush.table_name')`.

| Kolom | Catatan |
|---|---|
| `id` | PK |
| `subscribable_type` / `subscribable_id` | indeks morph `push_subscriptions_subscribable_morph_idx` |
| `endpoint` | string(500) unik |
| `public_key` | string nullable |
| `auth_token` | string nullable |
| `content_encoding` | string nullable |
| timestamps | |

`User` memakai trait `HasPushSubscriptions`, jadi subscription bertipe `subscribable_type = App\Models\User`.

### `cache`, `jobs`, `sessions`

Tabel framework Laravel default yang dibuat oleh `0001_01_01_000001_create_cache_table.php` dan `0001_01_01_000002_create_jobs_table.php`. Queue worker (saat `QUEUE_CONNECTION=database`) membaca dari `jobs`. `CACHE_STORE=database` membaca/menulis `cache` dan `cache_locks`.

---

## Kunci komposit, indeks, dan gotcha

- **Keunikan course**: komposit `(kode_matkul, semester_id, student_class_id)`. Validasi admin memakai bentuk komposit ini (dengan `dosen_id` juga diberlakukan inline). **Jangan** mengembalikan unique global pada `kode_matkul`.
- **Query mentah `enrollments`**: cari `DB::table('enrollments'` DAN `->enrollments()` sebelum mengganti nama kolom — kedua pola dipakai.
- **`exercise_config` adalah cast array**: dideklarasikan di `Assignment::casts()` sebagai `'exercise_config' => 'array'`. `$assignment->only(['exercise_config'])` mengembalikan array terdekode. Jangan harap `language`, `starter_code`, dst. adalah kolom asli.
- **Tidak ada kolom `auto_grade` pada assignments**: hanya `submissions.auto_graded` yang ada (per-submission, boolean).
- **Enum status konferensi `scheduled / live / ended`** — `live` adalah keadaan aktif, bukan `ongoing`. `Conference::isLive()` dan `isEnded()` adalah helper.
- **`MaterialView` menonaktifkan timestamps**: hanya `viewed_at` yang dilacak; `created_at`/`updated_at` tidak ada.
- **`discussion_comments.content`** — bukan `body`. Form Livewire mengikat `newComment` yang lalu ditulis ke `content`.
- **Nama ruang konferensi unik** di level DB; handler create membangun `room-{course_id}-{uuid()}` untuk menjaga jaminan itu.

---

## Menambah migrasi

```bash
php artisan make:migration add_x_to_y_table
```

Ikuti konvensi tanggal yang ada (`YYYY_MM_DD_HHMMSS_…`). Migrasi di repo ini memakai `Schema::table` untuk perubahan aditif dan `Schema::create` untuk tabel baru; aturan cascade dieja eksplisit (`cascadeOnDelete()` / `nullOnDelete()` / `set null`). Bila menambah indeks, cerminkan di method `down()`.

Setelah menulis migrasi, perbarui bagian relevan di dokumen ini agar referensi skema tidak melenceng.
