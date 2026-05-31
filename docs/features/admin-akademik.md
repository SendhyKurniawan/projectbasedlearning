# Admin — Akademik (unified hierarchy management)

## What it replaces

There used to be one CRUD per level (`/admin/academic-years`, `/admin/semesters`, `/admin/departments`, `/admin/study-programs`, `/admin/student-classes`) and a separate `/admin/hierarchy/…` drill-down. The `HierarchyController` has been **deleted** and the drill-down URLs now permanently redirect:

```php
// routes/web.php
Route::get('/admin/hierarchy/{any?}', fn () => redirect()->route('admin.akademik.index'))
    ->where('any', '.*')->middleware(['auth', 'role:admin']);
```

The unified replacement is `/admin/akademik`, served by `Admin\AkademikController`. The legacy per-level resource routes (`admin.academic-years.*`, `admin.semesters.*`, etc.) are still registered for back-compat — they continue to work for things like `Admin\AcademicYearController::index` rendering a flat list — but new admin work should go through `/admin/akademik`.

---

## Page structure

`GET /admin/akademik` (`admin.akademik.index`) renders a single page with the whole hierarchy navigable via query-string selection:

| Query param | Meaning |
|---|---|
| `?ay=<id>` | selected `AcademicYear` |
| `?sem=<id>` | selected `Semester` (must belong to the selected ay) |
| `?dep=<id>` | selected `Department` |
| `?prog=<id>` | selected `StudyProgram` (must belong to selected dep) |

`index()` resolves these in order — if any of them is omitted, it picks "active first, then first" as a default. The resulting payload to the view:

| Variable | Contents |
|---|---|
| `$academicYears` | all years with `semesters_count` |
| `$selectedAy` | resolved by `?ay` or active or first |
| `$semesters` | semesters of `$selectedAy`, ordered by `is_active DESC, name ASC` |
| `$selectedSem` | resolved by `?sem` or active or first |
| `$departments` | all departments with `study_programs_count` |
| `$selectedDep` | resolved by `?dep` or first |
| `$studyPrograms` | programs of `$selectedDep`, with `student_classes_count` |
| `$selectedProg` | resolved by `?prog` or first |
| `$classes` | kelas of `$selectedProg` + `$selectedSem`, with students eager-loaded |
| `$semesterCourses` | courses with `semester_id=$selectedSem.id` AND `student_class_id IS NULL` (semester-wide courses) |
| `$classCourses` | courses with `student_class_id IN $classes.ids` (per-kelas courses) |
| `$dosens` | all `role=dosen` users (for course-create dropdown) |
| `$availableStudents` | all `role=mahasiswa` users (for kelas-assign dropdown), only populated when a kelas is selected |

The result is one page that can edit any level inline. Each mutation route below redirects back to `admin.akademik.index` with the relevant query params preserved so the post-action view stays scoped to the same selection.

---

## Routes

All routes are under `Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')`.

### Academic year

```
POST   /admin/akademik/academic-years              akademik.academic-years.store
PUT    /admin/akademik/academic-years/{ay}         akademik.academic-years.update
DELETE /admin/akademik/academic-years/{ay}         akademik.academic-years.destroy
PATCH  /admin/akademik/academic-years/{ay}/activate akademik.academic-years.activate
```

Validation: `year_start` and `year_end` strings max 4, `is_active` boolean. Setting `is_active=true` deactivates every other AY in one bulk update. `activate` is a shortcut that does the same thing without re-validating other fields. `destroy` blocks if `semesters()->exists()`.

### Semester

```
POST   /admin/akademik/semesters                   akademik.semesters.store
PUT    /admin/akademik/semesters/{sem}             akademik.semesters.update
DELETE /admin/akademik/semesters/{sem}             akademik.semesters.destroy
PATCH  /admin/akademik/semesters/{sem}/activate    akademik.semesters.activate
```

Validation: `name` must be `Ganjil` or `Genap` (`Rule::in(['Ganjil','Genap'])`), `start_date` and `end_date` with `after:start_date`, `academic_year_id` must exist, `is_active` boolean. Like AY, `is_active=true` deactivates all other semesters. `destroy` blocks if any `courses()` OR `student_classes` reference the semester.

### Department (Jurusan)

```
POST   /admin/akademik/departments                 akademik.departments.store
PUT    /admin/akademik/departments/{dep}           akademik.departments.update
DELETE /admin/akademik/departments/{dep}           akademik.departments.destroy
```

Validation: `name` required, `code` unique on `departments.code` (ignoring self on update). `destroy` blocks if `studyPrograms()->exists()`.

### Study program (Prodi)

```
POST   /admin/akademik/study-programs              akademik.study-programs.store
PUT    /admin/akademik/study-programs/{prog}       akademik.study-programs.update
DELETE /admin/akademik/study-programs/{prog}       akademik.study-programs.destroy
```

Validation: `department_id` exists, `name` required, `code` unique on `study_programs.code` (ignore self), `level` in `D3|D4|S1|S2|S3`. `destroy` blocks if `studentClasses()->exists()`.

### Student class (Kelas)

```
POST   /admin/akademik/classes                                       akademik.classes.store
PUT    /admin/akademik/classes/{kelas}                               akademik.classes.update
DELETE /admin/akademik/classes/{kelas}                               akademik.classes.destroy
POST   /admin/akademik/classes/{kelas}/students                      akademik.classes.assign-students
DELETE /admin/akademik/classes/{kelas}/students/{user}               akademik.classes.unassign-student
```

Validation: `study_program_id`, `semester_id`, `name`. `destroy` blocks if `students()` OR `courses()` reference the kelas.

`assignStudents`:

```php
$request->validate([
    'student_ids' => 'required|array|min:1',
    'student_ids.*' => 'exists:users,id',
]);

// Filter: only users with role=mahasiswa actually move
$users = User::whereIn('id', $request->student_ids)->where('role', 'mahasiswa')->get();

if ($users->count() !== count($request->student_ids)) {
    return back()->with('error', 'Beberapa user yang dipilih bukan mahasiswa.');
}

User::whereIn('id', $users->pluck('id'))
    ->update(['student_class_id' => $studentClass->id]);
```

Note this bulk update **moves** the mahasiswa to the kelas (changing `users.student_class_id`), not just adds an enrollment. A mahasiswa has exactly one home kelas.

`unassignStudent` sets `user.student_class_id = null`. The 404-guard checks `$user->student_class_id === $studentClass->id` first.

### Course (Mata Kuliah)

```
POST   /admin/akademik/courses              akademik.courses.store
DELETE /admin/akademik/courses/{course}     akademik.courses.destroy
```

Course CRUD here only covers create + delete; full editing happens via `admin.courses.*` (legacy resource at `/admin/courses`). The store handler accepts a `scope` field:

- `scope=semester` — `student_class_id` is forced to null (semester-wide course; mahasiswa from any kelas in that semester can enroll)
- `scope=class` — `student_class_id` is taken from the request

```php
$exists = Course::where('kode_matkul', $kodeMatkul)
    ->where('dosen_id', $dosenId)
    ->where('semester_id', $semesterId)
    ->where(function ($q) use ($studentClassId) {
        if ($studentClassId) {
            $q->where('student_class_id', $studentClassId);
        } else {
            $q->whereNull('student_class_id');
        }
    })->exists();
```

The uniqueness check here is application-level rather than DB-level — it covers the four-column composite (incl. `dosen_id`), while the DB `courses_code_semester_class_unique` index only covers `(kode_matkul, semester_id, student_class_id)`. Two dosen can theoretically register the same course shape; the controller blocks that with the explicit check.

`destroy` blocks if the course has materials, assignments, or enrolled students — listing which to the operator.

---

## Legacy admin routes (kept for back-compat)

These still exist under `Route::middleware(['auth', 'role:admin'])`:

| Resource | Controller | Notes |
|---|---|---|
| `admin.academic-years.*` | `Admin\AcademicYearController` | Used to drive a flat AY list page |
| `admin.semesters.*` | `Admin\SemesterController` | flat list |
| `admin.departments.*` | `Admin\DepartmentController` | flat list |
| `admin.study-programs.*` | `Admin\StudyProgramController` | flat list |
| `admin.student-classes.*` | `Admin\StudentClassController` | flat list |
| `admin.courses.*` | `Admin\CourseController` | full CRUD + enroll/unenroll endpoints |
| `admin.users.*` | `Admin\UserController` | full CRUD + `toggleActive`, `bulkDestroy` |

These exist mainly for direct admin URLs the unified akademik page doesn't yet cover (full course edit, user management). They use the same `Rule::unique` composite for the `courses` table.

---

## Admin users

`Admin\UserController` (`/admin/users`) is a separate CRUD:

```
GET    /admin/users                            admin.users.index
GET    /admin/users/create                     admin.users.create
POST   /admin/users                            admin.users.store
GET    /admin/users/{user}/edit                admin.users.edit
PUT    /admin/users/{user}                     admin.users.update
DELETE /admin/users/{user}                     admin.users.destroy
DELETE /admin/users/bulk-destroy               admin.users.bulk-destroy
PATCH  /admin/users/{user}/toggle-active       admin.users.toggle-active
```

Filters on index: `?search=` (matches `name`/`email`/`nim`/`nip`), `?role=`, `?status=active|inactive` (mapped to `is_active=true|false`). Pagination 15/page.

The index counts pending dosen (`role=dosen && is_active=false`) for the activation queue badge. `toggleActive` is the action that flips `is_active` for a registered dosen waiting on admin approval — see [auth-roles.md](../auth-roles.md#admin-approval).

Validation in `store`:

```php
$validated = $request->validate([
    'name'     => ['required', 'string', 'max:255'],
    'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'role'     => ['required', 'string', 'in:admin,dosen,mahasiswa'],
    'nim'      => ['nullable', 'string', 'max:20', 'unique:users,nim'],
    'nip'      => ['nullable', 'string', 'max:20', 'unique:users,nip'],
    'student_class_id' => ['nullable', 'exists:student_classes,id'],
    'password' => ['required', 'confirmed', Password::defaults()],
]);

$validated['password']  = Hash::make($validated['password']);
$validated['is_active'] = true;  // admin-created users are active immediately, no OTP
```

`update` allows changing role and class; the password is hashed only if provided. `is_active` toggles via checkbox.

---

## Admin push debug

`/admin/debug/push` — see [notifications.md](notifications.md#admin-debug--manual-push).

---

## Admin conference observer

`/admin/conferences/*` — see [conferences.md](conferences.md#admin-adminconferencecontroller).
