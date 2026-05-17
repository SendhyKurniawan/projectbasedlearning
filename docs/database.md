# Database

## Domain Groups

```
Academic Hierarchy:
  academic_years → semesters → departments → study_programs → student_classes

People:
  users (role: admin | dosen | mahasiswa)

Learning:
  courses ── materials ── material_views
         ├── assignments ── quiz_questions ── quiz_options
         │              └── submissions
         ├── groups ── group_members
         └── conferences

Communication:
  discussions ── discussion_comments
  announcements
  notifications
  push_subscriptions
```

---

## Table Reference

### `users`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string | unique |
| `password` | string | bcrypt |
| `role` | string | `admin\|dosen\|mahasiswa` |
| `nim` | string nullable | Mahasiswa student ID |
| `nip` | string nullable | Dosen employee ID |
| `student_class_id` | FK nullable | Mahasiswa home class |
| `is_active` | boolean | Admin can toggle |

### `courses`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `nama_matkul` | string | Display name |
| `kode_matkul` | string | Subject code |
| `description` | text nullable | |
| `dosen_id` | FK → users | |
| `semester_id` | FK → semesters nullable | |
| `student_class_id` | FK → student_classes nullable | |
| `sks` | integer | Credit hours |
| `course_img` | string nullable | Uploaded banner |

**Unique constraint**: `(kode_matkul, dosen_id, semester_id, student_class_id)` — not globally unique on `kode_matkul` alone. A dosen can teach the same kode to different kelas in the same semester.

### `enrollments` (pivot)

| Column | Notes |
|---|---|
| `course_id` | FK → courses |
| `mahasiswa_id` | FK → users |
| `final_grade` | float nullable |
| `enrolled_at` | timestamp |

**Important**: Mahasiswa controllers use `DB::table('enrollments')->where(...)` raw queries for speed rather than going through the `Course::students()` Eloquent relation. If you change column names here, `grep -r "DB::table('enrollments'" app/` and update all call sites.

### `assignments`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `course_id` | FK → courses | |
| `title` | string | |
| `description` | text nullable | |
| `type` | string | `tugas\|quiz\|exercise` |
| `deadline` | datetime | |
| `max_score` | integer | |
| `order` | integer nullable | Display order within course |
| `assignment_number` | integer nullable | Sequential number for tugas |
| `quiz_number` | integer nullable | Sequential number for quiz |
| `submission_format` | string nullable | `pdf\|url` (tugas only) |
| `is_group` | boolean | Tugas group mode |
| `max_group_size` | integer nullable | |
| `grading_mode` | string nullable | `equal\|individual` (group tugas) |
| `duration_minutes` | integer nullable | Quiz time limit |
| `required_material_id` | FK → materials nullable | Prerequisite gate |
| `exercise_config` | JSON cast nullable | Exercise-only — see below |

**`exercise_config` JSON structure** (no standalone columns — always access via `$assignment->exercise_config['key']`):

```json
{
  "language": "python",
  "starter_code": "def solve(n):\n    pass",
  "solution_code": "def solve(n):\n    return n * 2",
  "required_keywords": ["def", "return"],
  "hints": ["Think about the return statement"]
}
```

### `submissions`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `assignment_id` | FK | |
| `mahasiswa_id` | FK → users | |
| `group_id` | FK → groups nullable | Group tugas only |
| `file_path` | string nullable | Tugas file upload |
| `url` | string nullable | Tugas URL submission |
| `score` | float nullable | Set by dosen grading |
| `status` | string | `submitted\|graded\|late` |
| `auto_graded` | boolean | True only for pure-MC quiz |
| `validation_result` | JSON nullable | Exercise hint — NOT a grade |
| `submitted_at` | timestamp | |

**`validation_result`** is the raw Piston API response from code execution. It's displayed to dosen as "Validasi Mesin" — a hint about keyword match results. It does not set `score` or change `status`.

### `quiz_questions` / `quiz_options`

`quiz_questions.type` ∈ `pilihan_ganda | essay | code_snippet`. Options exist only for `pilihan_ganda`. MC questions are auto-scored on submit; `essay` and `code_snippet` always require dosen review.

### `conferences`

| Column | Notes |
|---|---|
| `room_name` | string unique — used as the Jitsi room identifier |
| `status` | `scheduled\|ongoing\|ended` |
| `ended_at` | timestamp nullable |

### `notifications`

Standard Laravel `notifications` table. Type column contains the fully-qualified notification class name. `data` is JSON with the notification payload.

---

## Composite Keys and Gotchas

- **Course uniqueness**: The admin unique validation uses all four columns. Do not add a global unique index on `kode_matkul` alone.
- **`enrollments` raw queries**: The pattern `DB::table('enrollments')->where('course_id', $id)->where('mahasiswa_id', $userId)` appears across multiple mahasiswa controllers. If you rename a column, grep for it.
- **`exercise_config` is a cast**: It's an `array` cast in `Assignment::casts()`. `$assignment->only(['exercise_config'])` will return the decoded array, not the JSON string — that's correct. Don't try to add `language`, `starter_code` etc. as real columns.
- **No `auto_grade` column**: An earlier WIP design had it; it was reverted. The column doesn't exist — don't reference it.
