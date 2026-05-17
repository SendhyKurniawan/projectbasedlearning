# Assignments

## Three Types

`assignments.type` ∈ `tugas | quiz | exercise`. The type determines which submission flow mahasiswa use, which fields are relevant, and how grading works.

| Type | Submission | Auto-grade | Grade stored |
|---|---|---|---|
| `tugas` | File (PDF) or URL | No | Dosen manual via `grade` action |
| `quiz` | Answer form (timed) | MC only | Set on submit (MC) or after dosen review (essay/code) |
| `exercise` | Code solve form | Hint only | Dosen manual via `grade` action |

---

## Tugas

- `submission_format`: `pdf` or `url` — drives which form input appears on submission
- `is_group`: boolean — enables group formation before submission
- `max_group_size`: integer — cap on group size
- `grading_mode`: `equal | individual` — whether group members all get the same score or can be graded individually
- `assignment_number`: sequential display number within the course

Dosen grades via `Dosen\AssignmentController::grade` (POST `/dosen/submissions/{submission}/grade`). Score written to `submissions.score`, status set to `graded`.

---

## Quiz

- `quiz_number`: sequential display number for quizzes
- `duration_minutes`: time limit; null means unlimited
- Questions are `QuizQuestion` records linked to the assignment

**Question types** (`quiz_questions.type`):

| Type | Auto-scored |
|---|---|
| `pilihan_ganda` | Yes (MC) — checks `quiz_options.is_correct` |
| `essay` | No — requires dosen review |
| `code_snippet` | No — requires dosen review |

**Quiz flow** (mahasiswa):
1. `GET /mahasiswa/assignments/{assignment}/quiz` — info page
2. `POST /mahasiswa/assignments/{assignment}/quiz/start` — creates attempt record, starts timer
3. `GET /mahasiswa/assignments/{assignment}/quiz/take` — answer form
4. `POST /mahasiswa/assignments/{assignment}/quiz/submit` — scores MC, sets status
5. `GET /mahasiswa/assignments/{assignment}/quiz/result` — score display

On submit: if all questions are `pilihan_ganda`, `status = graded` and `auto_graded = true`. If any `essay` or `code_snippet` questions exist, `status = submitted` with provisional MC score — dosen must review.

**Copy behavior**: copying a quiz to sibling kelas creates a shell with no questions. Dosen must add questions to the copy separately (shown as a notice in the UI).

---

## Exercise

- `exercise_config`: JSON-cast column — the **only** place exercise-specific data lives

```php
// Access pattern — never treat these as standalone columns
$lang    = $assignment->exercise_config['language'];        // e.g. 'python'
$starter = $assignment->exercise_config['starter_code'];
$solution = $assignment->exercise_config['solution_code']; // server-side only
$keywords = $assignment->exercise_config['required_keywords']; // array
$hints   = $assignment->exercise_config['hints'];           // array
```

**Exercise flow** (mahasiswa):
1. `GET /mahasiswa/exercises/{assignment}/solve` — CodeMirror editor with starter code
2. "Run" button → `POST /execute-code` (throttled 10/min) → Piston API → returns stdout/stderr
3. "Submit" → `POST /mahasiswa/exercises/submit` → `ExerciseController::submit` → keyword validation

On submit: keyword match result is stored in `submissions.validation_result` (raw JSON). Score remains null, `auto_graded = false`, `status = submitted`. Dosen sees the validation hint as "Validasi Mesin" in the submissions view and grades manually.

**There is no `auto_grade` column**. An earlier design had it; it was reverted. Don't reference it.

---

## Material Prerequisite

Any assignment can require a student to have viewed a specific material first:

```php
$assignment->required_material_id;   // FK to materials, nullable
$assignment->isUnlockedFor($userId); // returns bool
```

`isUnlockedFor()` checks `MaterialView::where('material_id', ...)->where('student_id', ...)->exists()`. The `CheckAssignmentUnlocked` middleware calls this before submission routes. See [auth-roles.md](../auth-roles.md).

---

## Display Order Fields

Three different numbering fields exist because the UI surface changed over time:

| Field | Used for |
|---|---|
| `order` | Drag-and-drop display order within a course (all types) |
| `assignment_number` | Labeled "Tugas ke-N" in tugas views |
| `quiz_number` | Labeled "Kuis ke-N" in quiz views |

`order` is what the reorder endpoint updates. The number fields are set manually by dosen and are display-only.
