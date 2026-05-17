# Materials

## The Model

`Material` belongs to a `Course`. Key columns:

| Column | Notes |
|---|---|
| `course_id` | FK — the owning course |
| `title` | string |
| `content` | text — stored as HTML |
| `file_path` | string nullable — uploaded attachment |
| `order` | integer — display order, set by drag-and-drop |

`Course::materials()` orders by `order` ascending.

---

## Authoring (Dosen Side)

**Create/edit forms** use two different editors depending on the content type:

- **EasyMDE** (`markdown-editor.js`): for the main `content` field — produces Markdown. The controller converts to HTML before storage (via `str()->markdown()` or equivalent — check `Dosen\MaterialController::store`).
- **CodeMirror** (`code-editor.js`): available inline within the markdown editor for code blocks.

The create form also includes `@include('dosen.partials.sibling-kelas-picker')` for fan-out to sibling kelas at creation time.

**Reordering**: `POST /dosen/courses/{course}/materials/reorder` with an ordered array of IDs updates the `order` column. Drag-and-drop in the dosen material list view triggers this endpoint.

---

## Viewing (Mahasiswa Side)

`GET /mahasiswa/courses/{course}/materials/{material}` → `Mahasiswa\CourseController::showMaterial`.

The show view uses `markdown-renderer.js` to render the stored `content` client-side via `marked` + `highlight.js`. Viewing a material creates a `MaterialView` record — this is what unlocks prerequisite assignments.

---

## `material_views` and Prerequisite Unlocking

```php
// MaterialView record created when a student opens a material
MaterialView::firstOrCreate([
    'material_id' => $material->id,
    'student_id'  => auth()->id(),
]);
```

The existence of this record is the access signal for `Assignment::isUnlockedFor()`. If a student views a material then the material is later set as a prerequisite on an assignment, the student is already unlocked — `material_views` records are not retroactively cleared.

---

## File Handling and Fan-out

If a material has an attached file (`file_path`), the fan-out copy creates a **physically separate copy** of the file for each sibling kelas:

```
original:  materials/material_42_notes.pdf
sibling 1: materials/material_42_notes_kelas5_1716300000.pdf
sibling 2: materials/material_42_notes_kelas6_1716300001.pdf
```

The unique suffix (`_kelas{id}_{time()}`) prevents two sibling materials from sharing a path. Without this, deleting one sibling's material would remove the file used by all siblings.

When a material is deleted, only its own `file_path` is unlinked — not the originals of other siblings.
