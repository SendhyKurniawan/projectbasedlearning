# Courses

## The data model

One `Course` row = one matkul × one kelas × one semester × one dosen. This is intentional — every kelas gets its own materials/assignments/conferences tree without sharing rows across kelas.

Key columns (see [database.md](../database.md#courses) for the full schema):

- `nama_matkul`, `kode_matkul`, `sks`, `description`, `course_img`
- `dosen_id` (FK users), `semester_id` (FK semesters), `student_class_id` (FK student_classes)
- composite unique constraint `(kode_matkul, semester_id, student_class_id)` named `courses_code_semester_class_unique`. The earlier global `unique(kode_matkul)` was dropped by migration `2026_05_14_000001_relax_course_kode_matkul_unique`.

Relationships (`app/Models/Course.php`):

| Relation | Returns |
|---|---|
| `dosen()` | `belongsTo(User)` on `dosen_id` |
| `semester()` | `belongsTo(Semester)` |
| `studentClass()` | `belongsTo(StudentClass)` on `student_class_id` |
| `materials()` | `hasMany(Material)->orderBy('order')` |
| `assignments()` | `hasMany(Assignment)->orderBy('order')` |
| `students()` | `belongsToMany(User, 'enrollments', 'course_id', 'mahasiswa_id')->withPivot('final_grade', 'enrolled_at')` |
| `conferences()` | `hasMany(Conference)` |

The reverse-side `User::courses()` (taught) and `User::enrollments()` / `User::enrolledCourses()` (enrolled, with alias) are defined on `User`.

---

## Siblings

A dosen teaching the same `kode_matkul` to two kelas in the same semester gets two `Course` rows. These are **siblings**.

```php
// Returns Collection<Course> — excludes self, eager-loads studentClass, ordered by student_class_id
$siblings = $course->siblings();

// Used to populate copy-to-kelas checkboxes
$siblingIds = $course->siblings()->pluck('id');
```

The match is `dosen_id + kode_matkul + semester_id`, ignoring `student_class_id`. Result is memoized per instance via `$cachedSiblings` so repeated calls within one request are free.

The unique constraint on `courses` is composite — a dosen *can* legitimately have multiple rows with the same `kode_matkul`, as long as `student_class_id` differs. Admin form validation enforces the composite uniqueness.

---

## `course_group_key` and the sidebar

The sidebar and dashboards need to visually group sibling courses under one accordion. The grouping key is an accessor on the model:

```php
// Course::getCourseGroupKeyAttribute()
return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
```

Notice it uses **`nama_matkul`** (display name), not `kode_matkul`. Two courses with the same display name but different codes (a legacy renamed course) will cluster together — intentional.

`SidebarComposer` caches the resolved dosen course list:

```php
$dosenCourses = Cache::remember("sidebar:dosen:{$user->id}", 300, fn () =>
    Course::where('dosen_id', $user->id)
        ->with('studentClass')
        ->orderBy('created_at', 'desc')
        ->get()
);
$dosenCourseGroups = $dosenCourses->groupBy('course_group_key');
```

The `Course::booted()` hook flushes `sidebar:dosen:{dosen_id}` on every create/update/delete. If the sidebar isn't refreshing after a course change, verify the `dosen_id` on the course matches the logged-in user.

---

## Enrollment

### Admin-side

`Admin\CourseController` exposes the full resource CRUD at `/admin/courses` and dedicated enrollment endpoints:

| Method + URL | Action |
|---|---|
| `POST /admin/courses/{course}/enroll` | `Admin\CourseController@enroll` |
| `DELETE /admin/courses/{course}/enroll/{student}` | `Admin\CourseController@unenroll` |

Admin can also manage assignments to kelas through `/admin/akademik` — see `Admin\AkademikController::assignStudents()` and `unassignStudent()` for the kelas-side moves.

### Mahasiswa self-enrollment

`Mahasiswa\CourseController::enroll` accepts `POST /mahasiswa/courses/{course}/enroll`. There is currently no public/private gate on courses — any logged-in mahasiswa can enroll in any course they can reach via `/mahasiswa/courses`. If you need to gate self-enrollment, add the check there.

```php
$alreadyEnrolled = DB::table('enrollments')
    ->where('mahasiswa_id', $mahasiswa->id)
    ->where('course_id', $course->id)
    ->exists();

if ($alreadyEnrolled) {
    return redirect()->back()->with('info', 'Anda sudah terdaftar di course ini.');
}

$mahasiswa->enrollments()->attach($course->id, ['enrolled_at' => now()]);
```

### Reading enrollments

Two coexisting patterns — both are correct, but if you change the `enrollments` schema, grep for both. See [database.md](../database.md#enrollments).

```php
// Raw query (used by most mahasiswa controllers for speed)
$isEnrolled = DB::table('enrollments')
    ->where('mahasiswa_id', $mahasiswa->id)
    ->where('course_id', $course->id)
    ->exists();

// Eloquent relation (used by ScheduleController, SubmissionController, DashboardController)
$courseIds = $mahasiswa->enrollments()->pluck('courses.id');
$isEnrolled = $mahasiswa->enrollments()->where('courses.id', $course->id)->exists();
```

---

## Mahasiswa course-show view

`Mahasiswa\CourseController::show` (`GET /mahasiswa/courses/{course}`) builds a "learning path" combining materials and assignments. The view loads:

- `$course->materials` ordered by `order`
- `$course->assignments` with `questions` + `requiredMaterial`, ordered by `order` then `deadline`
- `$viewedMaterialIds` from `MaterialView`
- `$submissions` keyed by `assignment_id`

The learning path is constructed by `buildLearningPath()`:

```
For each material (in order):
  emit { type: material, item, completed: viewed, locked: false }
  if an assignment has required_material_id === material.id:
    emit { type: assignment, item, completed: has_submission, locked: !viewed }

After all materials, emit any assignments with required_material_id IS NULL.
```

This drives the UI's "complete this material to unlock the next assignment" experience. The CSS gating (`locked: true`) is purely visual — the server-side gate is `CheckAssignmentUnlocked` middleware on the submission/exercise-solve routes.

---

## Copy fan-out

When a dosen copies a material, assignment, conference, or exercise to sibling kelas, the controller intersects submitted `sibling_ids` against actual siblings before acting:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn($id) => (int) $id)
    ->intersect($allowedSiblingIds);

foreach (Course::whereIn('id', $targetIds)->get() as $sibling) {
    // copy to $sibling
}
```

This is the security boundary — without it, a crafted POST could target other dosens' courses. The `<x-copy-modal>` Blade component renders the sibling-checkbox UI. See [contributing.md](../contributing.md) for the rule.

| Feature | Create with fan-out | Copy after create |
|---|---|---|
| Material | `Dosen\MaterialController::store` | `materials.copy` → `Dosen\MaterialController::copy` |
| Assignment | `Dosen\AssignmentController::store` | `assignments.copy` → `Dosen\AssignmentController::copy` |
| Conference | `Dosen\ConferenceController::store` | `conferences.copy` → `Dosen\ConferenceController::copy` |
| Exercise | `Dosen\ExerciseController::store` | (no dedicated copy endpoint — use assignment copy) |

See feature pages for the per-type fan-out behaviour ([materials](materials.md), [assignments](assignments.md), [conferences](conferences.md)).

---

## Dosen sections and "bare URL" fallbacks

Dosen has a few sections that need a course context (Materials, Assignments, Exercises, Conferences). The sidebar links to them under the dosen's first course. For convenience, bare URLs without a course parameter exist:

| Bare route | Behaviour |
|---|---|
| `GET /dosen/materials` (`dosen.materials.bare`) | redirect to `dosen.materials.index` for the dosen's first course, or render `dosen.no-course` view |
| `GET /dosen/assignments` (`dosen.assignments.bare`) | same, for assignments |
| `GET /dosen/exercises` (`dosen.exercises.bare`) | redirects to assignments index (exercises and assignments share the index) |
| `GET /dosen/conferences` (`dosen.conferences.bare`) | same, for conferences |

These are defined inline in `routes/web.php` with a closure that picks the first owned course.

---

## Admin course validation

Admin enforces the composite uniqueness — see `Admin\CourseController::store/update` and `Admin\AkademikController::storeCourse`. When creating multiple courses with the same `kode_matkul` for different kelas in the same semester, validation must accept the duplication on `kode_matkul` alone but reject it on the full tuple.
