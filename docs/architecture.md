# Architecture

## The Three-Role System

Every user has a single `users.role` enum value. The root `/` route redirects to the appropriate dashboard immediately on login. There is no shared landing page.

```
                    ┌─────────────────┐
                    │   / (root)      │
                    │ redirects by    │
                    │ users.role      │
                    └────────┬────────┘
             ┌───────────────┼───────────────┐
             ▼               ▼               ▼
    /admin/dashboard  /dosen/dashboard  /mahasiswa/dashboard
    middleware:        middleware:       middleware:
    auth + role:admin  auth + role:dosen auth + role:mahasiswa
```

**Admin** — manages the academic hierarchy, users, and courses. Cannot create learning content.  
**Dosen** — owns courses (one row per kelas). Creates materials, assignments, quizzes, exercises, conferences.  
**Mahasiswa** — enrolled in courses. Views content, submits assignments, takes quizzes, attends conferences.

Shared auth-only routes (no role restriction): `/profile`, `/notifications`, `/discussions`, `/announcements`, `/execute-code` (throttled), `/push-subscribe`.

See [auth-roles.md](auth-roles.md) for middleware internals and policy details.

---

## Academic Hierarchy

```
AcademicYear
  └── Semester
        └── Department
              └── StudyProgram
                    └── StudentClass
                          └── Course  (dosen_id, kode_matkul, semester_id, student_class_id)
```

Admin manages the entire hierarchy via the `/admin/akademik` unified page. A `StudentClass` groups mahasiswa for enrollment purposes. One `Course` row = one kelas of one matkul in one semester taught by one dosen.

---

## Sibling Courses and `course_group_key`

A dosen teaching the same `kode_matkul` to multiple kelas gets one `Course` row per kelas. These rows are called *siblings* — they share `dosen_id + kode_matkul + semester_id` but differ on `student_class_id`.

```php
// Course::siblings() — excludes self, memoized per request
$siblings = $course->siblings(); // Collection<Course>

// Sidebar grouping key
$key = $course->course_group_key; // "{dosen_id}|{nama_matkul}|{semester_id}"
```

The sidebar and dashboard use `course_group_key` to visually cluster sibling courses under one accordion entry. The `SidebarComposer` caches the course list per dosen (`sidebar:dosen:{id}`) and the cache is flushed on every Course create/update/delete via the model's `booted()` hook.

---

## Request Data Flow

```
Browser → Nginx (brotli/gzip) → PHP-FPM (app container)
  → Laravel Router → Middleware stack → Controller
    → Model / DB → Blade → Response
```

No Pusher, no WebSockets, no Reverb. `BROADCAST_CONNECTION=log`. Push notifications use WebPush (VAPID) via the `webpush` channel — server-initiated one-way.

---

## Key Conventions at a Glance

| Decision | Rule |
|---|---|
| Validation | Inline `$request->validate([...])` in controller — no FormRequest except `LoginRequest` and `ProfileUpdateRequest` |
| Authorization | `$this->authorize('view', $model)` via Policies — no Gates |
| Pivot queries | `DB::table('enrollments')` raw queries for performance; Eloquent relation on `Course::students()` exists but may not be used everywhere. **Exception**: `Mahasiswa\ScheduleController` uses `$mahasiswa->enrollments()->pluck('courses.id')` via Eloquent |
| Livewire | One real component: `Livewire\Discussion\Show`. Everything else is plain Blade + Alpine |
| Boolean fields | Validate as `'nullable\|in:0,1,true,false'` because HTML forms send strings |
| Fan-out security | Always intersect submitted `sibling_ids` with `$course->siblings()->pluck('id')` before acting |

---

## Feature Map

- [courses.md](features/courses.md) — Course model, siblings, enrollment, copy fan-out
- [assignments.md](features/assignments.md) — tugas / quiz / exercise types, grading
- [materials.md](features/materials.md) — content, file handling, prerequisite gating
- [submissions.md](features/submissions.md) — submission flows, groups, status lifecycle
- [conferences.md](features/conferences.md) — Jitsi JaaS JWT, room lifecycle
- [notifications.md](features/notifications.md) — database + WebPush channels
- [discussions.md](features/discussions.md) — Livewire component, comment model
- [schedule.md](features/schedule.md) — mahasiswa upcoming conferences and assignment deadlines (`/mahasiswa/jadwal`)
