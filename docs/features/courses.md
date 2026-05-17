# Courses

## The Data Model

One `Course` row = one matkul × one kelas × one semester × one dosen. This is intentional — it gives each kelas its own material/assignment tree without sharing.

Key columns: `nama_matkul`, `kode_matkul`, `sks`, `dosen_id`, `semester_id`, `student_class_id`, `course_img`.

Relationships: `dosen` (User), `semester`, `studentClass`, `materials` (ordered by `order`), `assignments` (ordered by `order`), `students` (via `enrollments`), `conferences`.

---

## Siblings

A dosen teaching the same `kode_matkul` to two kelas in the same semester gets two `Course` rows. These are *siblings*.

```php
// Returns Collection<Course> — excludes self, memoized per request
$siblings = $course->siblings();

// Used to populate copy-to-kelas checkboxes in views
$siblingIds = $course->siblings()->pluck('id');
```

`siblings()` matches on `dosen_id + kode_matkul + semester_id`, not `student_class_id`. The result is memoized via `$cachedSiblings` so repeated calls within one request are free.

The unique constraint on `courses` is composite: `(kode_matkul, dosen_id, semester_id, student_class_id)`. Admin validation enforces this — see [database.md](../database.md).

---

## `course_group_key` and the Sidebar

The sidebar and dashboard need to visually group sibling courses under one accordion. The grouping key is computed:

```php
// Course::getCourseGroupKeyAttribute()
return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
```

Note: it uses `nama_matkul` (display name), not `kode_matkul`. This means two courses with the same display name but different codes will cluster together — intentional for legacy courses that got renamed.

The `SidebarComposer` caches the resolved course list in `Cache::remember("sidebar:dosen:{$dosenId}", ...)`. The `Course` model's `booted()` hook calls `Cache::forget("sidebar:dosen:{$course->dosen_id}")` on create, update, and delete. If the sidebar isn't refreshing after a course change, check that the dosen_id on the course matches the logged-in user's id.

---

## Enrollment

Mahasiswa are enrolled per course by admin via `Admin\CourseController::enroll` (POST `/admin/courses/{course}/enroll`). The `enrollments` pivot stores `mahasiswa_id`, `course_id`, `final_grade`, and `enrolled_at`.

Mahasiswa can self-enroll via `Mahasiswa\CourseController::enroll` (POST `/mahasiswa/courses/{course}/enroll`) if the course is public — check the controller for any enrollment guard logic.

The Eloquent relation is `Course::students()` via `belongsToMany('enrollments', 'course_id', 'mahasiswa_id')`. However, most mahasiswa-side lookups use `DB::table('enrollments')` raw queries — see [database.md](../database.md) for why.

---

## Copy Fan-out

When dosen copies a material, assignment, or conference to sibling kelas, the copy action must intersect submitted IDs against actual siblings:

```php
$validSiblingIds = $course->siblings()->pluck('id')->toArray();
$targetIds = array_intersect($request->sibling_ids ?? [], $validSiblingIds);
```

This prevents a crafted POST from targeting another dosen's courses. The `<x-copy-modal>` component renders the checkboxes for sibling selection in the UI. See [contributing.md](../contributing.md) for the full security rule.
