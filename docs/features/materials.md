# Materials

## The model

`Material` belongs to a `Course`. Schema:

| Column | Notes |
|---|---|
| `id` | PK |
| `course_id` | FK courses cascade, indexed |
| `title` | string |
| `content` | text nullable — Markdown source, **not** HTML |
| `file_path` | string nullable — uploaded attachment on the `public` disk under `materials/…` |
| `order` | integer default 0 — drag-and-drop reorder |
| timestamps | |

`Material::views()` is `hasMany(MaterialView)`. The helper `hasBeenViewedBy($studentId)` is a quick existence check.

`Course::materials()` orders by `order` ascending.

---

## Dosen authoring routes

```
GET    /dosen/courses/{course}/materials                  materials.index
GET    /dosen/courses/{course}/materials/create           materials.create
POST   /dosen/courses/{course}/materials                  materials.store
POST   /dosen/courses/{course}/materials/reorder          materials.reorder
GET    /dosen/materials/{material}/edit                   materials.edit
PUT    /dosen/materials/{material}                        materials.update
DELETE /dosen/materials/{material}                        materials.destroy
POST   /dosen/materials/{material}/copy                   materials.copy
```

All gated by `CoursePolicy::update` (so dosen owns the course, or admin).

### Create / edit

The create form uses:

- **EasyMDE** (`markdown-editor.js`) for the `content` field — produces Markdown. Stored as-is in `materials.content`; rendered client-side on mahasiswa view.
- **CodeMirror** inline within EasyMDE for code blocks.

The form also includes `@include('dosen.partials.sibling-kelas-picker')` for fan-out at create time. The `store()` validation:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'content' => 'nullable|string',
    'file' => 'nullable|file|max:20480',          // 20 MB cap
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

File upload is stored under `materials/` on the `public` disk:

```php
$filename = time() . '_' . $file->getClientOriginalName();
$file_path = $file->storeAs('materials', $filename, 'public');
```

The new material's `order` is `($course->materials()->max('order') ?? 0) + 1` — appended at the end.

### Reorder

`POST /dosen/courses/{course}/materials/reorder` accepts an ordered array of IDs:

```php
$request->validate([
    'ordered_ids' => 'required|array',
    'ordered_ids.*' => 'exists:materials,id',
]);

$order = 1;
foreach ($request->ordered_ids as $id) {
    Material::where('id', $id)
            ->where('course_id', $course->id)   // re-scope to prevent cross-course writes
            ->update(['order' => $order]);
    $order++;
}
```

Drag-and-drop in the dosen material list view triggers this. The handler returns JSON.

### Update

`PUT /dosen/materials/{material}` overwrites `title`, `content`, and optionally `file_path`. If a new file is uploaded, the old `file_path` is deleted from the `public` disk first.

### Destroy

`DELETE /dosen/materials/{material}` deletes the file (if any) then deletes the row. Cascade on `course_id` ensures no orphan rows when a course is deleted.

### Notification side effects

`store()`, `update()`, and store-with-fan-out all dispatch `AcademicUpdateNotification` to every enrolled mahasiswa of the affected course (and each sibling kelas's mahasiswa for fan-out copies). See [notifications.md](notifications.md).

---

## File handling and fan-out

If a material has an attached file (`file_path`), the fan-out copy creates a **physically separate copy** of the file for each sibling kelas:

```
original (course=42):  materials/1716300000_notes.pdf
sibling kelas=5:       materials/1716300000_notes_kelas5.pdf       (on create-fanout)
sibling kelas=6:       materials/1716300000_notes_kelas6.pdf
later copy to kelas=7: materials/1716300000_notes_kelas7_1716300999.pdf   (extra _{time()} suffix)
```

Why? If two sibling materials shared the same path, deleting one would `Storage::delete()` the file used by the others. The unique suffix prevents that.

When a material is deleted (`destroy`), only its own `file_path` is unlinked. The originals belonging to other siblings are untouched.

The copy logic (`Dosen\MaterialController::copy`):

```php
$ext  = pathinfo($material->file_path, PATHINFO_EXTENSION);
$stem = pathinfo($material->file_path, PATHINFO_FILENAME);
$newPath = 'materials/' . $stem . '_kelas' . $sibling->id . '_' . time() . '.' . $ext;
Storage::disk('public')->copy($material->file_path, $newPath);
```

The security intersect runs first — only siblings the dosen actually owns are targeted.

---

## Mahasiswa view

`GET /mahasiswa/courses/{course}/materials/{material}` → `Mahasiswa\CourseController::showMaterial`.

The handler:

1. Verifies enrollment via `DB::table('enrollments')`.
2. Loads the material by id (scoped through `$course->materials()`).
3. **Records the view** via `MaterialView::updateOrCreate(['material_id', 'student_id'], ['viewed_at' => now()])`.
4. Rebuilds the learning path (same `buildLearningPath()` as the course-show view).
5. Renders `mahasiswa.materials.show` with `material`, `learningPath`, `nextItem`, `prevItem`, `currentIndex`.

The view uses `markdown-renderer.js` to render the stored Markdown content client-side via marked + highlight.js. The "next / prev" navigation is computed server-side from the learning-path index.

---

## `material_views` and prerequisite unlocking

```php
MaterialView::updateOrCreate(
    [
        'material_id' => $material->id,
        'student_id'  => $mahasiswa->id,
    ],
    [
        'viewed_at' => now(),
    ]
);
```

The unique key `(material_id, student_id)` keeps it idempotent. The model has `$timestamps = false` so only `viewed_at` is tracked.

The existence of the row is the unlock signal for `Assignment::isUnlockedFor()`:

```php
public function isUnlockedFor($studentId): bool
{
    if (!$this->required_material_id) {
        return true;
    }

    return MaterialView::where('material_id', $this->required_material_id)
        ->where('student_id', $studentId)
        ->exists();
}
```

`CheckAssignmentUnlocked` middleware calls this on submission/exercise-solve routes — see [auth-roles.md](../auth-roles.md). Views set the prerequisite are not retroactively cleared if a student viewed the material before it was set as a prerequisite — the student is already unlocked.
