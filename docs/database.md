# Database

## Domain groups

```
Academic Hierarchy
  academic_years ── semesters ── student_classes ── users(role=mahasiswa)
                                       │
                                       └── (student_classes has semester_id + study_program_id)
  departments ── study_programs ─────────┘

People
  users (enum role: admin | dosen | mahasiswa)
    - nim (unique nullable, mahasiswa)
    - nip (unique nullable, dosen)
    - student_class_id (mahasiswa home class)
    - is_active, otp_*

Learning
  courses                     (dosen_id, semester_id, student_class_id)
    ├── materials             (order)
    │     └── material_views  (per student, viewed_at)
    ├── assignments           (type: tugas|quiz|exercise, exercise_config JSON,
    │                          required_material_id, is_group, ...)
    │     ├── submissions     (per-mahasiswa, optionally group_id)
    │     ├── quiz_questions ── quiz_options
    │     └── groups ── group_members
    ├── conferences           (status: scheduled|live|ended)
    └── enrollments (pivot, mahasiswa_id)

Communication
  discussions ── discussion_comments
  announcements ── announcement_user (pivot for target_audience='specific')
  notifications  (Laravel UUID notifiable_morph)
  push_subscriptions (laravel-notification-channels/webpush)
```

The default `DB_CONNECTION` in `.env.example` is `mysql` (matching the `db` service in `docker-compose.yml`). SQLite is used in Pest's `RefreshDatabase` feature tests.

---

## Table reference

### `users` (`0001_01_01_000000_create_users_table.php`, `2026_05_25_000000_add_otp_to_users_table.php`, `2026_03_08_063233_add_hierarchy_to_users_and_courses_table.php`, `2026_03_12_063456_add_indexes_to_core_tables.php`)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string | unique |
| `role` | enum | `mahasiswa\|dosen\|admin`, default `mahasiswa` |
| `nim` | string nullable | unique — mahasiswa student ID |
| `nip` | string nullable | unique — dosen employee ID |
| `sso_id` | string nullable | reserved for future SSO integration; nothing reads it yet |
| `is_active` | boolean | default `true` (registrations override to `false` until OTP/admin approval) |
| `email_verified_at` | timestamp nullable | |
| `password` | string | bcrypt, `password` cast as `hashed` |
| `otp_code` | string(6) nullable | 6-digit OTP for registration verification |
| `otp_expires_at` | timestamp nullable | 10 minutes after issue |
| `otp_verified_at` | timestamp nullable | dosen sets this then waits for admin to flip `is_active` |
| `remember_token` | rememberToken | |
| `student_class_id` | foreignId nullable | mahasiswa home class; null-on-delete |
| timestamps + indexes on `role`, `is_active`, `student_class_id` | | |

Companion tables: `password_reset_tokens(email PK, token, created_at)`, `sessions(id PK, user_id, ip_address, user_agent, payload, last_activity)`.

### `academic_years` / `semesters` / `departments` / `study_programs` / `student_classes`

| Table | Columns |
|---|---|
| `academic_years` | `id`, `year_start`, `year_end`, `is_active`, timestamps |
| `semesters` | `id`, `academic_year_id` FK cascade, `name` ("Ganjil"/"Genap"), `start_date`, `end_date`, `is_active`, timestamps. Index on `academic_year_id`. |
| `departments` | `id`, `name`, `code` unique, timestamps |
| `study_programs` | `id`, `department_id` FK cascade, `name`, `code` unique, `level` enum (`D3\|D4\|S1\|S2\|S3`, default `D4`), timestamps |
| `student_classes` | `id`, `study_program_id` FK cascade, `semester_id` FK cascade, `name`, timestamps. Indexes on both FKs. |

### `courses` (`2026_02_01_031051_create_courses_table.php`, `2026_03_08_063233_add_hierarchy_…`, `2026_05_14_000001_relax_course_kode_matkul_unique.php`, indexes)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `kode_matkul` | string | originally `unique`; the unique was **dropped** by `relax_course_kode_matkul_unique` |
| `nama_matkul` | string | display name; the basis of `course_group_key` |
| `sks` | integer | default `3` |
| `dosen_id` | foreignId users.id, cascade | |
| `semester_id` | foreignId nullable, null-on-delete | |
| `course_img` | string nullable | banner upload |
| `description` | text nullable | |
| `student_class_id` | foreignId nullable, null-on-delete | added later by `add_hierarchy_…` |
| timestamps + indexes on `dosen_id`, `semester_id`, `student_class_id` | | |
| **Composite unique** | `(kode_matkul, semester_id, student_class_id)` named `courses_code_semester_class_unique` | one course per (code + semester + kelas). A dosen can teach the same kode to different kelas in the same semester. |

### `enrollments` (`2026_02_01_031103_create_enrollments_table.php`, indexes)

| Column | Notes |
|---|---|
| `id` | bigint PK |
| `course_id` | FK courses, cascade |
| `mahasiswa_id` | FK users, cascade |
| `final_grade` | decimal(5,2) nullable |
| `enrolled_at` | timestamp default CURRENT |
| timestamps + indexes on both FKs | |
| **Unique** | `(course_id, mahasiswa_id)` — prevents duplicate enrollment |

**Important convention**: most mahasiswa controllers query enrollments via `DB::table('enrollments')->where(...)->exists()` instead of going through the `User::enrollments()` belongsToMany relation. The exceptions are `Mahasiswa\ScheduleController`, `Mahasiswa\SubmissionController`, and `Mahasiswa\DashboardController` which use the relation directly. **If you rename a column in this table, grep for both patterns**:

```bash
grep -r "DB::table('enrollments'" app/
grep -r "enrollments()" app/
```

### `materials` (`2026_02_01_031054_create_materials_table.php`, indexes)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `course_id` | foreignId cascade | indexed |
| `title` | string | |
| `content` | text nullable | stored as Markdown by EasyMDE; rendered client-side via `markdown-renderer.js` |
| `file_path` | string nullable | path on the `public` disk under `materials/…` |
| `order` | integer default 0 | drag-and-drop reorder via `materials/reorder` |
| timestamps | | |

### `material_views` (`2026_02_01_091419_create_material_views_table.php`)

| Column | Notes |
|---|---|
| `id` | bigint PK |
| `material_id` | FK materials, cascade |
| `student_id` | FK users, cascade |
| `viewed_at` | timestamp |
| **Unique** | `(material_id, student_id)` — one row per student per material |
| Index | `student_id` |

`MaterialView::$timestamps = false` (only `viewed_at` is tracked). The model exposes a `mahasiswa()` relation aliasing `student_id`.

### `assignments` (`2026_02_01_031057_create_assignments_table.php`, `2026_03_19_231831_add_order_to_assignments_table.php`, `2026_05_04_120000_add_group_fields_to_assignments_and_groups.php`)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `course_id` | foreignId cascade | indexed |
| `title` | string | |
| `order` | integer default 0 | drag-and-drop reorder |
| `assignment_number` | integer nullable | shown as "Tugas ke-N" |
| `description` | text nullable | |
| `type` | enum | `tugas\|quiz\|exercise`, default `tugas` |
| `submission_format` | enum | `pdf\|url`, default `pdf` (only meaningful for tugas) |
| `exercise_config` | JSON nullable | array cast — exercise-only payload (see below) |
| `deadline` | datetime | |
| `max_score` | integer default 100 | |
| `required_material_id` | foreignId materials nullable, null-on-delete | prerequisite gate |
| `duration_minutes` | integer nullable | quiz timer; null = unlimited |
| `quiz_number` | integer nullable | shown as "Kuis ke-N" |
| `is_group` | boolean default false | tugas group mode |
| `max_group_size` | unsignedInteger nullable | includes the submitter |
| `grading_mode` | enum | `equal\|individual`, default `equal` |
| timestamps | | |

**`exercise_config` JSON structure** (array-cast, no standalone columns):

```json
{
  "language": "java",
  "starter_code": "public class Main { public static void main(String[] a) { } }",
  "solution_code": "public class Main { ... }",
  "required_keywords": ["public", "static", "void", "main"],
  "hints": ["Print to stdout", "Use System.out.println"]
}
```

Access via `$assignment->exercise_config['language']`. There is no `auto_grade` column — an earlier WIP design had it but was reverted.

### `submissions` (`2026_02_01_031100_create_submissions_table.php`, `2026_03_01_000000_add_code_fields_to_submissions_table.php`, `2026_02_15_000000_create_quiz_and_group_tables.php` adds `group_id`, indexes)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `assignment_id` | foreignId cascade | indexed |
| `group_id` | foreignId groups nullable, null-on-delete | added by quiz_and_group migration |
| `mahasiswa_id` | foreignId users cascade | indexed |
| `file_path` | string nullable | tugas PDF upload (`submissions/{time}_{user_id}_{name}`) |
| `url_link` | string nullable | tugas URL submission |
| `answers` | JSON nullable | quiz answers map `{question_id: option_id_or_text}` |
| `notes` | text nullable | mahasiswa note |
| `submitted_at` | timestamp nullable | |
| `started_at` | timestamp nullable | quiz timer start |
| `finished_at` | timestamp nullable | quiz submit time |
| `score` | integer nullable | |
| `feedback` | text nullable | dosen feedback |
| `status` | enum | `submitted\|late\|graded`, default `submitted` |
| `code_answer` | text nullable | exercise solve code |
| `validation_result` | JSON nullable | array-cast Piston/keyword validation hint |
| `auto_graded` | boolean default false | true only for pure-MC quiz |
| timestamps | | |

**`validation_result`** is a hint, not a grade. Stored shape from `Mahasiswa\ExerciseController::validateCode()`:

```json
{
  "passed": false,
  "score": 60,
  "feedback": "Missing required element: return\nFound 3/5 required elements.",
  "validated_at": "2026-05-28 10:30:00"
}
```

Dosen sees it as "Validasi Mesin" in the submissions view but assigns the real score manually. The "passed/score" inside the JSON is informational only — the column `score` on the submission stays `null` until graded.

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
| **Unique** | `(group_id, mahasiswa_id)` named `group_members_group_mahasiswa_unique` |

Only the `created_by_mahasiswa_id` can edit or delete the group's submission (`Mahasiswa\SubmissionController` enforces this).

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

Options are populated only for `pilihan_ganda` questions. The submit handler auto-scores MC by summing `score_weight` for each question where the submitted option has `is_correct=true`.

### `conferences` (`2026_03_01_000001_create_conferences_table.php`)

| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `course_id` | FK courses cascade | |
| `dosen_id` | FK users cascade | who created/owns the session |
| `title` | string | |
| `description` | text nullable | |
| `room_name` | string **unique** | generated as `room-{course_id}-{uuid}`; passed as the Jitsi room identifier |
| `scheduled_at` | datetime | |
| `ended_at` | datetime nullable | |
| `status` | enum | `scheduled\|live\|ended`, default `scheduled` |
| timestamps | | |

The valid status values are **`scheduled`, `live`, `ended`** — there is no `ongoing` status, despite older docs that may say otherwise.

### `discussions` / `discussion_comments`

`discussions` was originally course-scoped (FK `course_id`); the `2026_03_12_065022_alter_discussions_table_replace_course_with_topic.php` migration dropped that FK and added a free-text `topic` column.

| `discussions` | |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — the author |
| `topic` | string — categorizes discussion (free text; popular topics are aggregated for the sidebar) |
| `title` | string |
| `content` | text |
| timestamps | |

| `discussion_comments` | |
|---|---|
| `id` | PK |
| `discussion_id` | FK discussions cascade |
| `user_id` | FK users cascade |
| `content` | text — **not** `body` |
| timestamps | |

### `announcements` / `announcement_user` (`2026_03_12_065146_create_announcements_table.php`, `2026_03_12_065149_create_announcement_user_table.php`, `2026_04_18_000000_add_attachment_to_announcements_table.php`)

| `announcements` | |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — the author |
| `title` | string |
| `content` | text |
| `target_audience` | enum (`all\|dosen\|mahasiswa\|specific`), default `all` |
| `attachment_path` / `attachment_name` / `attachment_mime` | added later for file attachments |
| timestamps | |

| `announcement_user` | (pivot for `target_audience='specific'`) |
|---|---|
| `id` | PK |
| `announcement_id` | FK cascade |
| `user_id` | FK cascade |
| timestamps | |
| Unique | `(announcement_id, user_id)` |

### `notifications` (`2026_03_12_065659_create_notifications_table.php`)

Standard Laravel database-channel notifications.

| Column | Notes |
|---|---|
| `id` | UUID PK |
| `type` | fully-qualified notification class name |
| `notifiable_type` / `notifiable_id` | polymorphic morph |
| `data` | text (JSON-encoded payload from `toArray()`) |
| `read_at` | timestamp nullable |
| timestamps | |

### `push_subscriptions` (`2026_03_19_235123_create_push_subscriptions_table.php`)

Provided by the `laravel-notification-channels/webpush` package; the migration uses `config('webpush.database_connection')` and `config('webpush.table_name')`.

| Column | Notes |
|---|---|
| `id` | PK |
| `subscribable_type` / `subscribable_id` | morph index `push_subscriptions_subscribable_morph_idx` |
| `endpoint` | string(500) unique |
| `public_key` | string nullable |
| `auth_token` | string nullable |
| `content_encoding` | string nullable |
| timestamps | |

`User` uses the `HasPushSubscriptions` trait, so subscriptions are `subscribable_type = App\Models\User`.

### `cache`, `jobs`, `sessions`

Default Laravel framework tables created by `0001_01_01_000001_create_cache_table.php` and `0001_01_01_000002_create_jobs_table.php`. The queue worker (when `QUEUE_CONNECTION=database`) reads from `jobs`. `CACHE_STORE=database` reads/writes `cache` and `cache_locks`.

---

## Composite keys, indexes, and gotchas

- **Course uniqueness**: composite `(kode_matkul, semester_id, student_class_id)`. Admin validation uses this composite shape (with `dosen_id` also enforced inline). Do **not** restore a global unique on `kode_matkul`.
- **`enrollments` raw queries**: search for `DB::table('enrollments'` AND `->enrollments()` before changing column names — both patterns are in use.
- **`exercise_config` is an array cast**: declared in `Assignment::casts()` as `'exercise_config' => 'array'`. `$assignment->only(['exercise_config'])` returns a decoded array. Never expect `language`, `starter_code`, etc. to be real columns.
- **No `auto_grade` column on assignments**: only `submissions.auto_graded` exists (per-submission, boolean).
- **Conference status enum is `scheduled / live / ended`** — `live` is the active state, not `ongoing`. `Conference::isLive()` and `isEnded()` are helpers.
- **`MaterialView` disables timestamps**: only `viewed_at` is tracked; `created_at`/`updated_at` do not exist.
- **`discussion_comments.content`** — not `body`. The Livewire form binds `newComment` which is then written to `content`.
- **Conference room name is unique** at the DB level; the create handler builds `room-{course_id}-{uuid()}` to keep that guarantee.

---

## Adding a migration

```bash
php artisan make:migration add_x_to_y_table
```

Follow the existing date convention (`YYYY_MM_DD_HHMMSS_…`). Migrations in this repo use `Schema::table` for additive changes and `Schema::create` for new tables; cascading rules are spelled out explicitly (`cascadeOnDelete()` / `nullOnDelete()` / `set null`). If you add an index, mirror it in the `down()` method.

After writing the migration, update the relevant section in this document so the schema reference doesn't drift.
