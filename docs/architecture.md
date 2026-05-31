# Architecture

## What this app is

**PBL Workspace** is a server-rendered Laravel 12 LMS for Project-Based Learning. Every page is Blade with Tailwind + Alpine sprinkles. There is exactly one Livewire component (`App\Livewire\Discussion\Show`). There is no SPA, no Inertia, no Pusher/Reverb. Domain terms are in Indonesian (`mata_kuliah`, `kode_matkul`, `sks`, `nim`, `nip`, `dosen`, `mahasiswa`) — that is intentional, do not anglicise.

---

## The three-role system

Every user has exactly one value in `users.role` (a MySQL enum):

| Value | Route prefix | Dashboard route | Middleware |
|---|---|---|---|
| `admin` | `/admin` | `admin.dashboard` | `auth` + `role:admin` |
| `dosen` | `/dosen` | `dosen.dashboard` | `auth` + `role:dosen` |
| `mahasiswa` | `/mahasiswa` | `mahasiswa.dashboard` | `auth` + `role:mahasiswa` |

The root `/` route inspects `auth()->user()->role` and redirects to the matching dashboard. Guests are sent to `/login`. There is no shared landing page.

```
                    ┌─────────────────┐
                    │   /             │
                    │ redirect by     │
                    │ users.role      │
                    └────────┬────────┘
             ┌───────────────┼───────────────┐
             ▼               ▼               ▼
    /admin/dashboard  /dosen/dashboard  /mahasiswa/dashboard
```

**Admin** manages the academic hierarchy (years, semesters, departments, study programs, kelas), all users, and global course records. Admin cannot author learning content.

**Dosen** owns courses (one row per kelas) and authors learning content — materials, assignments (tugas/quiz/exercise), conferences. Dosen grades submissions.

**Mahasiswa** is enrolled in courses via the `enrollments` pivot. Mahasiswa views content, submits work, takes quizzes, joins conferences when they go live.

### Shared auth-only routes (any role)

- `GET /profile` / `PATCH /profile` / `DELETE /profile` — Breeze profile controller
- `GET /notifications`, `POST /notifications/mark-all-read`, `POST /notifications/{id}/mark-read`, `GET /notifications/{id}/redirect` — `NotificationController`
- `POST /execute-code` — `CodeExecutionController` (throttled `throttle:10,1`)
- `POST /push-subscribe`, `POST /push-unsubscribe` — `PushSubscriptionController`
- `Route::resource('discussions', ...)` — discussions CRUD
- `Route::resource('announcements', ...)` — announcements CRUD

See [auth-roles.md](auth-roles.md) for middleware internals and policy details.

---

## Academic hierarchy

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
- `departments` (`name`, `code` unique)
- `study_programs` (`department_id`, `name`, `code` unique, `level` = D3/D4/S1/S2/S3)
- `student_classes` (`study_program_id`, `semester_id`, `name`) — a cohort within one semester
- `courses` — one row per (dosen, matkul, kelas, semester) tuple

Admin maintains the whole tree in one unified page at `/admin/akademik` (`Admin\AkademikController`), with sub-actions for create/update/delete/activate on each level. The old per-level CRUD routes (`/admin/academic-years`, `/admin/departments`, etc.) are still registered via `Route::resource()` for back-compat but the unified page is the primary UI. The deprecated `/admin/hierarchy/*` URLs redirect to `/admin/akademik`.

---

## Sibling courses and `course_group_key`

A dosen teaching the same `kode_matkul` to multiple kelas in the same semester gets one `Course` row per kelas. These rows are **siblings** — same `dosen_id + kode_matkul + semester_id`, different `student_class_id`.

```php
// Course::siblings() — excludes self, memoized per request via $cachedSiblings
$siblings = $course->siblings();  // Illuminate\Support\Collection<Course>

// Stable grouping key used by sidebar / dashboard
$key = $course->course_group_key;
// "{dosen_id}|{nama_matkul}|{semester_id}"  ← intentionally uses nama_matkul, not kode_matkul
```

Why `nama_matkul` and not `kode_matkul` in the group key? Legacy courses with renamed codes still cluster correctly. See `Course::getCourseGroupKeyAttribute()` (app/Models/Course.php:121).

### Sidebar cache

`SidebarComposer` (registered in `AppServiceProvider::boot()` for view `layouts.sidebar`) caches the dosen course list under `Cache::remember("sidebar:dosen:{$dosenId}", 300, ...)`. The `Course` model's `booted()` hook flushes that key on every create/update/delete:

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

If the sidebar isn't refreshing after a course change, check that the `dosen_id` on the course matches the logged-in user.

### Mahasiswa sidebar lookup

For mahasiswa, the composer does a single raw query to pick the first enrolled course (used as the "go to my course" jump link):

```php
$mahasiswaFirstCourse = DB::table('enrollments')
    ->where('mahasiswa_id', $user->id)
    ->value('course_id');
```

---

## Copy fan-out (Material / Assignment / Conference / Exercise)

Dosen actions that publish content come in two flavours:

1. **Create with fan-out** — the create form includes `@include('dosen.partials.sibling-kelas-picker')` which renders checkboxes for sibling kelas. `store()` creates the primary record, then loops over the validated `sibling_ids` to fan out copies. Used in: Material, Assignment, Conference, Exercise.
2. **Copy after create** — a `<x-copy-modal>` opens from the existing record's view and POSTs `sibling_ids` to a dedicated `copy` endpoint. Used in: `materials.copy`, `assignments.copy`, `conferences.copy`.

Both paths apply the same security intersect server-side:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn ($id) => (int) $id)
    ->intersect($allowedSiblingIds);
// only iterate $targetIds — never the raw input
```

Without the intersect, a crafted POST could target another dosen's courses. See [contributing.md](contributing.md) for the rule.

**Quiz copy** creates a shell — no questions are copied. The success message tells the dosen to add questions to each copy separately (questions often need kelas-specific adjustments).

**Material file copy** physically duplicates the uploaded file with a unique suffix (`_kelas{id}` on create-fanout, `_kelas{id}_{time()}` on later copy) so deleting one sibling's material doesn't unlink the file used by the others.

---

## Request flow

```
Browser
  → Caddy on host VM (HTTPS, Let's Encrypt)
  → Nginx (alpine, container) — serves /build/* assets with long cache,
    passes app requests to PHP-FPM via FastCGI on app:9000
  → Laravel router → middleware stack → controller
    → Eloquent / DB → Blade → response
  → optional: queue worker picks up ShouldQueue notifications (DB driver) or fires inline (sync driver)
```

There is no WebSocket layer. `BROADCAST_CONNECTION=log` writes broadcast events to the log file and nothing else — do not call `broadcast()` from new code. Push notifications are the only real-time delivery channel, sent server-initiated via VAPID WebPush.

---

## Code execution sandbox (Piston)

Mahasiswa "Run" button on exercise solve pages and dosen preview buttons proxy code through `POST /execute-code` (throttled 10/min, auth required). `CodeExecutionController::execute()` validates the language against `config('code_execution.piston_language_map')` — currently `java`, `php`, `csharp` (with C# routed to Piston's `csharp.net` runtime). It then POSTs to `${PISTON_URL}/execute` with the user's code and returns `{stdout, stderr, exit_code}`. Connection / non-2xx errors return HTTP 502 with `Execution service unavailable.`

The Docker compose bundles `ghcr.io/engineer-man/piston` as a `piston` service on the internal network; `.env.example` points `PISTON_URL` at `http://piston:2000/api/v2`. The config-level fallback is the public `https://emkc.org/api/v2/piston`.

Exercise editor languages on the dosen side cover a wider set (`html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`); the HTML/CSS/JS ones render in a client-side preview iframe instead of going through Piston.

---

## Key conventions at a glance

| Decision | Rule |
|---|---|
| Validation | Inline `$request->validate([...])` in controller. The only FormRequests are `LoginRequest` and `ProfileUpdateRequest` (Breeze). |
| Authorization | `$this->authorize('verb', $model)` via Policies — no Gates, no inline `abort(403)` role checks. |
| Pivot queries | Mahasiswa controllers use `DB::table('enrollments')->where(...)` raw queries for speed. Exception: `ScheduleController`, `SubmissionController`, `Mahasiswa\DashboardController` use the `User::enrollments()` belongsToMany relation. |
| Livewire | One real component: `App\Livewire\Discussion\Show`. Everything else is plain Blade. |
| Boolean form fields | Validate as `'nullable\|boolean'` and cast in the model. Forms send `"1"`/`"0"` strings; the `boolean` rule accepts those plus actual booleans. |
| Fan-out security | Always intersect submitted `sibling_ids` with `$course->siblings()->pluck('id')`. |
| Lazy-loading | `Model::preventLazyLoading(!app()->isProduction())` is set in `AppServiceProvider::boot()` — N+1 lazy loads throw in local/staging, log silently in production. |

---

## Feature map

- [courses.md](features/courses.md) — Course model, siblings, enrollment, copy fan-out
- [materials.md](features/materials.md) — content authoring, file handling, `MaterialView`, prerequisite gating
- [assignments.md](features/assignments.md) — tugas / quiz / exercise types, grading flows, question types
- [submissions.md](features/submissions.md) — submission lifecycle, group submissions, validation hints
- [conferences.md](features/conferences.md) — Jitsi self-hosted HS256 JWT, room lifecycle, new-tab launcher
- [notifications.md](features/notifications.md) — database + WebPush channels, queueing
- [discussions.md](features/discussions.md) — the lone Livewire component
- [schedule.md](features/schedule.md) — mahasiswa upcoming view at `/mahasiswa/jadwal`

Operations:

- [ops/jitsi-self-host.md](ops/jitsi-self-host.md) — GCP VM, Caddy, self-hosted Jitsi stack
- [deployment.md](deployment.md) — production checklist, env vars, queues, mail
- [getting-started.md](getting-started.md) — local dev, env reference, seeded accounts
- [testing.md](testing.md) — Pest, Dusk scaffolding, Playwright WIP
- [contributing.md](contributing.md) — code conventions, security boundaries
- [auth-roles.md](auth-roles.md) — middleware, policies, OTP/registration flow
- [database.md](database.md) — schema reference, gotchas
- [frontend.md](frontend.md) — Vite entry points, layout, Alpine patterns, design system
