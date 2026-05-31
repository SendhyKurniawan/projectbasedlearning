# Assignments

## Three types

`assignments.type` is an enum with values `tugas | quiz | exercise`. The type drives the submission flow, which fields apply, and how grading works.

| Type | Mahasiswa submits | Auto-grade | Score path |
|---|---|---|---|
| `tugas` | PDF file or URL | No | Dosen grades via `Dosen\AssignmentController::grade` (or `gradeGroup` for group tugas) |
| `quiz` | Quiz form (timed if `duration_minutes` set) | MC only | Pure-MC quizzes are auto-graded on submit; quizzes with `essay`/`code_snippet` questions get a provisional MC score, dosen reviews via `showQuizAttempt` |
| `exercise` | Code via CodeMirror | No (only a hint) | Dosen grades via `grade`; the keyword-match hint goes into `submissions.validation_result` |

The full assignment column list is in [database.md](../database.md#assignments). Notable fields:

- `order` — drag-and-drop order within course (all types)
- `assignment_number` — sequential display ("Tugas/Latihan ke-N", for `tugas` and `exercise`)
- `quiz_number` — sequential display ("Kuis ke-N", quiz only)
- `submission_format` — `pdf|url` (tugas only; for non-tugas, store-time forces `pdf`)
- `is_group`, `max_group_size`, `grading_mode` — group tugas settings
- `duration_minutes` — quiz time limit; null = unlimited
- `required_material_id` — prerequisite gate; if set, students must have a `MaterialView` for that material
- `exercise_config` — JSON-cast array, exercise-only payload (`language`, `starter_code`, `solution_code`, `required_keywords[]`, `hints[]`)

---

## Dosen authoring routes

### Generic (tugas + quiz)

```
GET    /dosen/courses/{course}/assignments                 assignments.index
GET    /dosen/courses/{course}/assignments/create          assignments.create
POST   /dosen/courses/{course}/assignments                 assignments.store
POST   /dosen/courses/{course}/assignments/reorder         assignments.reorder
GET    /dosen/assignments/{assignment}/edit                assignments.edit
PUT    /dosen/assignments/{assignment}                     assignments.update
DELETE /dosen/assignments/{assignment}                     assignments.destroy
POST   /dosen/assignments/{assignment}/copy                assignments.copy
GET    /dosen/assignments/{assignment}/submissions         assignments.submissions
POST   /dosen/submissions/{submission}/grade               submissions.grade
POST   /dosen/groups/{group}/grade                         groups.grade
```

### Quiz question management

```
GET    /dosen/assignments/{assignment}/questions               assignments.questions.index
GET    /dosen/assignments/{assignment}/questions/create        assignments.questions.create
POST   /dosen/assignments/{assignment}/questions               assignments.questions.store
GET    /dosen/questions/{question}/edit                        assignments.questions.edit
PUT    /dosen/questions/{question}                             assignments.questions.update
DELETE /dosen/questions/{question}                             assignments.questions.destroy
GET    /dosen/assignments/{assignment}/submissions/{submission} assignments.submissions.show
```

### Exercise (dedicated, separate from generic create)

```
GET    /dosen/courses/{course}/exercises/create  exercises.create
POST   /dosen/courses/{course}/exercises         exercises.store
GET    /dosen/exercises/{assignment}/edit        exercises.edit
PUT    /dosen/exercises/{assignment}             exercises.update
```

The exercise CRUD has its own flow because the form is dominated by the CodeMirror starter/solution editors + the comma-separated `required_keywords` and newline-separated `hints` inputs.

All authoring routes are gated by `AssignmentPolicy` or `CoursePolicy::update` — see [auth-roles.md](../auth-roles.md).

---

## Tugas

### Fields

- `submission_format`: `pdf` or `url` — drives the mahasiswa submission form input
- `is_group` (boolean) — enables group formation
- `max_group_size` (integer, includes submitter) — cap on group size
- `grading_mode`: `equal` (one score for all members) or `individual` (per-member input)
- `assignment_number`: sequential display number

### Create / update

Validation in `Dosen\AssignmentController::store`:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'deadline' => 'required|date|after:now',
    'max_score' => 'required|integer|min:1|max:100',
    'type' => 'required|in:tugas,quiz,exercise',
    'has_duration' => 'nullable|boolean',
    'submission_format' => 'nullable|in:pdf,url',
    'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
    'is_group' => 'nullable|boolean',
    'max_group_size' => 'nullable|integer|min:2|max:20',
    'grading_mode' => 'nullable|in:equal,individual',
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

After creation, the controller dispatches `AcademicUpdateNotification` to all enrolled mahasiswa of the course (and of each sibling kelas if fan-out is selected). Group settings are **locked once submissions exist** — `update()` ignores `is_group`, `max_group_size`, `grading_mode` if `$assignment->submissions()->exists()`.

The `assignment_number` is computed per type per course: count existing rows of the same `type`, +1. The same applies to `quiz_number` for quiz type.

### Grading

`POST /dosen/submissions/{submission}/grade` (single-student tugas):

```php
$submission->update([
    'score' => $request->score,
    'feedback' => $request->feedback,
    'status' => 'graded',
]);
// then: Notification::send($student, new GradeNotification($assignment->title, $course->id));
```

`POST /dosen/groups/{group}/grade` for group tugas:

- `grading_mode = equal` — one `$request->score` written to every submission in the group
- `grading_mode = individual` — `$request->scores[mahasiswa_id]` and `$request->feedbacks[mahasiswa_id]` per member, only members whose `score` is set get `status=graded`

Both flows send `GradeNotification` to graded members.

### Quick-grade (grade book)

`PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade` — inline single-cell edit on the grade-book view (`Dosen\GradeController::quickGrade`). Uses `updateOrCreate` so it can create a submission row on the fly for a student who never submitted:

```php
Submission::updateOrCreate(
    ['assignment_id' => $assignment->id, 'mahasiswa_id' => $mahasiswa->id, 'group_id' => null],
    ['score' => $data['score'], 'feedback' => $data['feedback'] ?? null, 'status' => 'graded']
);
```

`submitted_at` is set on first grade if absent so the row isn't permanently null-timestamped.

---

## Quiz

### Question types (`quiz_questions.question_type`)

| Type | Auto-scored |
|---|---|
| `pilihan_ganda` | Yes — `QuizController::submit` adds `score_weight` for each question where the submitted `answers[question_id]` matches an option with `is_correct=true`. |
| `essay` | No — dosen reviews. |
| `code_snippet` | No — dosen reviews. |

Options exist only for `pilihan_ganda`. `score_weight` per question defaults to 1.

### Mahasiswa flow

```
GET  /mahasiswa/assignments/{assignment}/quiz          quizzes.show
POST /mahasiswa/assignments/{assignment}/quiz/start    quizzes.start
GET  /mahasiswa/assignments/{assignment}/quiz/take     quizzes.take
POST /mahasiswa/assignments/{assignment}/quiz/submit   quizzes.submit
GET  /mahasiswa/assignments/{assignment}/quiz/result   quizzes.result
```

1. `show` — info page. If a submission has `finished_at`, redirect straight to `result`.
2. `start` — `Submission::firstOrCreate({assignment, mahasiswa}, {started_at: now()})`. Redirect to `take`.
3. `take` — render the answer form. If already finished, redirect back to `show`.
4. `submit` — score MC questions inside a DB transaction, write `answers` JSON, set `finished_at = now()`, set `score` to the MC subtotal.
   - **All questions are pilihan_ganda** → `status = graded` (final).
   - **Any essay or code_snippet** → `status = submitted` (provisional MC score, awaiting dosen).
5. `result` — render the score, optionally with reveal of correct answers.

The MC scoring is a single query per submit — collect the submitted option ids, fetch the rows with `is_correct=true`, then iterate the question collection adding `score_weight` for each match.

### Dosen review of quiz attempts

`GET /dosen/assignments/{assignment}/submissions` for a quiz routes to `dosen.assignments.quiz_attempts` view (`Dosen\AssignmentController::submissions`). Each row links to:

`GET /dosen/assignments/{assignment}/submissions/{submission}` (`assignments.submissions.show`) which renders `dosen.assignments.quiz_attempt_show` — per-question with correct answer marked. Dosen can then go to `submissions.grade` to set the final score on essay/code_snippet quizzes.

### Quiz copy behaviour

Copying a quiz creates a **shell** assignment with no questions. The success message tells the dosen to add questions separately. This is intentional: question sets often need kelas-specific tweaks.

---

## Exercise

### `exercise_config` JSON structure

```json
{
  "language": "java",
  "starter_code": "public class Main { ... }",
  "solution_code": "public class Main { ... }",
  "required_keywords": ["public", "static", "void", "main"],
  "hints": ["Print to stdout", "Use System.out.println"]
}
```

Cast in `Assignment::casts()` as `'exercise_config' => 'array'`. Access via `$assignment->exercise_config['key']`. The `Dosen\ExerciseController` form posts:

- `exercise_language` → mapped to `language`
- `required_keywords` → comma-separated string → array via `array_map('trim', explode(',', …))`
- `hints` → newline-separated string → array

Allowed `exercise_language` values: `html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`. Only `java`, `php`, `csharp` can proxy to Piston via `/execute-code` (see `config/code_execution.php` and [architecture.md](../architecture.md#code-execution-sandbox-piston)). The HTML/CSS/JS ones run in a client-side preview iframe.

### Mahasiswa flow

```
GET  /mahasiswa/exercises/{assignment}/solve    exercises.solve  (check.assignment.unlocked)
POST /mahasiswa/exercises/submit                exercises.submit
```

1. `solve` — render `mahasiswa.exercises.solve` with the CodeMirror editor pre-populated from `starter_code`. Existing submission (if any) is loaded for display.
2. The "Run" button → `POST /execute-code` (`CodeExecutionController`, throttled 10/min). Returns `{stdout, stderr, exit_code}`.
3. "Submit" → `POST /mahasiswa/exercises/submit`. The controller validates `code_answer`, runs `validateCode()` (keyword match), and stores everything:

```php
Submission::create([
    'assignment_id' => $assignment->id,
    'mahasiswa_id' => $mahasiswa->id,
    'code_answer' => $request->code_answer,
    'validation_result' => $validationResult,
    'auto_graded' => false,
    'score' => null,
    'status' => 'submitted',
    'submitted_at' => now(),
]);
```

The `validation_result` shape:

```json
{
  "passed": false,
  "score": 60,
  "feedback": "Missing required element: return\nFound 3/5 required elements.",
  "validated_at": "2026-05-28 10:30:00"
}
```

This is a **hint shown to dosen** as "Validasi Mesin" in the submissions view (`resources/views/dosen/assignments/submissions.blade.php`). It does **not** set `score` or change `status`. There is no `auto_grade` column — an earlier WIP design had it; it was reverted.

Dosen grades the exercise manually via `Dosen\AssignmentController::grade` → `GradeNotification`.

---

## Material prerequisite

Any assignment can require a student to view a specific material first:

```php
$assignment->required_material_id;          // FK to materials, nullable
$assignment->isUnlockedFor($userId);        // bool
$assignment->requiredMaterial;              // belongsTo(Material)
```

`isUnlockedFor()` returns `true` if `required_material_id` is null, otherwise checks for a `MaterialView` row. `CheckAssignmentUnlocked` middleware applies this on:

```php
Route::resource('submissions', Mahasiswa\SubmissionController::class)
    ->except(['index', 'show'])
    ->middleware('check.assignment.unlocked');

Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])
    ->middleware('check.assignment.unlocked');
```

Failure redirects back with `error` flash. See [auth-roles.md](../auth-roles.md#checkassignmentunlocked-middleware).

---

## Display order fields — why three?

| Field | Used for |
|---|---|
| `order` | Drag-and-drop position within course (all types). Updated by `assignments.reorder`. |
| `assignment_number` | "Tugas ke-N" or "Latihan ke-N" label, only used in tugas/exercise views. |
| `quiz_number` | "Kuis ke-N" label, only used in quiz views. |

The number fields are set on create as `count(matching-type-in-course) + 1` and are display-only — they don't update on reorder. `order` is the canonical sort key.

---

## Copy fan-out

`POST /dosen/assignments/{assignment}/copy` copies an assignment to selected sibling kelas. Same security intersect as elsewhere — see [contributing.md](../contributing.md).

Fields copied: `title`, `description`, `deadline`, `max_score`, `type`, `submission_format`, `duration_minutes`, `is_group`, `max_group_size`, `grading_mode`, `exercise_config`.

Per copy:

- `course_id` = sibling
- `assignment_number` = `(count of same-type assignments in sibling) + 1`
- `quiz_number` = same logic for quizzes
- `order` = `(sibling.assignments.max('order') ?? 0) + 1`

If `type === 'quiz'`, the success message reminds the dosen to add questions in each sibling separately.

---

## Notifications

Every mutation that affects mahasiswa visibility dispatches an `AcademicUpdateNotification`:

| Action | Recipients | Title |
|---|---|---|
| `store` (non-quiz) | enrolled mahasiswa of `$course` | "Tugas/Exercise Baru Ditambahkan" |
| `store` fan-out per sibling | enrolled mahasiswa of sibling | same |
| `update` | enrolled mahasiswa of `$course` | "Tugas/Exercise Diperbarui" |
| `grade` | the student | `GradeNotification` |
| `gradeGroup` | all graded members | `GradeNotification` |

See [notifications.md](notifications.md) for the channel/queue semantics.
