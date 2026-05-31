# Submissions

## Lifecycle

```
                  (new submission)
                        │
                        ▼
                  ┌─────────────┐
                  │  submitted  │
                  └──────┬──────┘
                         │  dosen grades
                         ▼
                  ┌─────────────┐
                  │   graded    │
                  └─────────────┘

(quiz submit, all-pilihan-ganda)
                        │
                        ▼
                  ┌─────────────┐
                  │   graded    │   (status=graded set inline; final MC score)
                  └─────────────┘

(quiz submit, mixed/essay/code_snippet)
                        │
                        ▼
                  ┌─────────────┐
                  │  submitted  │   (provisional MC score; dosen reviews)
                  └─────────────┘
```

The `late` status exists on the enum (`status` column is `enum('submitted','late','graded')`) but **no controller currently sets it**. Submission handlers create rows with the default `submitted` and only `quickGrade`/`grade`/`gradeGroup` flip to `graded`. If you need to flag late submissions, add the check at submit time:

```php
'status' => now()->greaterThan($assignment->deadline) ? 'late' : 'submitted',
```

---

## Tugas submission

### Mahasiswa routes (`Mahasiswa\SubmissionController`)

The resource is registered with `->except(['index', 'show'])` and the `check.assignment.unlocked` middleware:

```
GET    /mahasiswa/submissions/create        submissions.create
POST   /mahasiswa/submissions               submissions.store
GET    /mahasiswa/submissions/{submission}/edit  submissions.edit
PUT    /mahasiswa/submissions/{submission}   submissions.update
DELETE /mahasiswa/submissions/{submission}   submissions.destroy
```

### `create` (GET)

Reads `?assignment_id=` from the query string, loads the assignment + course, validates enrollment, then:

- If the assignment is `is_group` and the user is already in a group for this assignment, load the group with members + creator.
- If `is_group` and not in a group, load `classmates` — enrolled mahasiswa not already in a group for this assignment. Renders a picker so the submitter can form the group at submit time.

Renders `mahasiswa.submissions.create` with `assignment`, `existing` submission (if any), `existingGroup`, `classmates`.

### `store` (POST)

Validation depends on `submission_format`:

```php
$rules = [
    'assignment_id' => 'required|exists:assignments,id',
    'notes' => 'nullable|string',
];

if ($assignment->submission_format === 'url') {
    $rules['url_link'] = 'required|url|max:2048';
} else {
    $rules['file'] = 'required|file|max:10240';   // 10 MB cap
}

if ($assignment->is_group) {
    $rules['member_ids'] = 'required|array|min:1';
    $rules['member_ids.*'] = 'integer|exists:users,id';
    $rules['group_name'] = 'nullable|string|max:120';
}
```

Then:

1. Re-check enrollment via `enrollments()->where('courses.id', ...)`.
2. Bail with an error flash if a submission for this user+assignment already exists.
3. Save the file (path: `submissions/{time()}_{user_id}_{name}`) on the `public` disk or set `url_link`.
4. **If group**: validate every selected member is enrolled in the course AND not already a member of another group for this assignment AND group size (including submitter) ≤ `max_group_size`. Wrap in a transaction:
   - Create `Group` with `created_by_mahasiswa_id = submitter`.
   - For each member (including submitter): create a `GroupMember` + a `Submission` row with the same `file_path`/`url_link`/`notes`.
   - Send `AcademicUpdateNotification` to the OTHER members ("Ditambahkan ke Kelompok").
5. **If individual**: a single `Submission` row.
6. Send `SubmissionNotification` to the course's dosen.

Group submissions are mirrored across rows so that grading reflects on every member, while still letting each member's view show their own submission row.

### `edit`, `update`

Block if `score !== null` (already graded). For group submissions, only the `created_by_mahasiswa_id` (group creator) can edit.

On update, file or URL is replaced. For group submissions, the mirror update applies to **every row in the group**:

```php
if ($submission->group_id) {
    Submission::where('group_id', $submission->group_id)->update($data);
}
```

### `destroy`

Same gating as `edit` + `update`. For group: only the creator can delete, and the delete cascades to all `Submission` rows + `GroupMember` rows + the `Group` itself. The file (if any) is unlinked first.

---

## Quiz submission

Handled by `Mahasiswa\QuizController`, not `SubmissionController`. See [assignments.md#quiz](assignments.md#quiz) for the full route list and behaviour.

Key submission semantics:

- `start` creates a `Submission` via `firstOrCreate(['assignment_id','mahasiswa_id'], ['started_at' => now()])`.
- `submit` writes `answers` (JSON map `{question_id: option_id_or_text}`), sets `finished_at = now()`, sets `score` to the MC subtotal, and sets `status`:
  - All questions are `pilihan_ganda` → `status = graded`.
  - Any `essay`/`code_snippet` present → `status = submitted` (provisional).
- A mahasiswa can only complete the quiz once — the `finished_at` check in `take()` blocks re-entry.

`auto_graded` is set to `true` only for the pure-MC path (and only by future code; right now `submit()` does not toggle the column — verify if you depend on it).

---

## Exercise submission

Handled by `Mahasiswa\ExerciseController::submit` (`POST /mahasiswa/exercises/submit`):

```php
$request->validate([
    'assignment_id' => 'required|exists:assignments,id',
    'code_answer' => 'required|string',
]);

// Verify enrollment, reject if already submitted.

$validationResult = $this->validateCode($assignment, $request->code_answer);

Submission::create([
    'assignment_id' => $assignment->id,
    'mahasiswa_id' => $mahasiswa->id,
    'code_answer' => $request->code_answer,
    'validation_result' => $validationResult,
    'auto_graded' => false,
    'score' => null,
    'feedback' => null,
    'status' => 'submitted',
    'submitted_at' => now(),
]);
```

The "Run" button on the solve page goes to `POST /execute-code` separately and does **not** create a submission. Only the explicit "Submit" creates the row.

### `validation_result`

Computed by `ExerciseController::validateCode($assignment, $code)`:

```php
foreach ($exercise_config['required_keywords'] as $keyword) {
    if (stripos($code, $keyword) !== false) {
        $foundKeywords++;
    } else {
        $passed = false;
        $feedback[] = "Missing required element: {$keyword}";
    }
}
$score = floor(($foundKeywords / $totalKeywords) * $assignment->max_score);

return [
    'passed' => $score === $maxScore,
    'score' => $score,
    'feedback' => implode("\n", $feedback),
    'validated_at' => now()->toDateTimeString(),
];
```

Result is shown to dosen as "Validasi Mesin" in `resources/views/dosen/assignments/submissions.blade.php`. **It does not set `score` or `status`.** The dosen sets the real grade through `submissions.grade`.

---

## Group assignments

| Model | Purpose |
|---|---|
| `Group` (`groups` table) | One per (assignment, team). Has `assignment_id`, `group_name`, `created_by_mahasiswa_id`. |
| `GroupMember` (`group_members` table) | Pivot: `group_id`, `mahasiswa_id`. Unique on `(group_id, mahasiswa_id)`. |

`Group` exposes: `assignment()`, `members()`, `submissions()`, `creator()` (the `created_by_mahasiswa_id` user), `hasMember($userId)`.

Only `created_by_mahasiswa_id` can edit/delete the group's submission. Members can view but can't mutate the submission rows.

Grading is done through `Dosen\AssignmentController::gradeGroup` (`POST /dosen/groups/{group}/grade`). Two modes set on the assignment:

- `grading_mode = equal` — `$request->score` and `$request->feedback` write to every submission in the group (one shared score).
- `grading_mode = individual` — `$request->scores[mahasiswa_id]` and `$request->feedbacks[mahasiswa_id]` per row. Only members whose `score` is set (`!== null && !== ''`) get `status = graded`. The rest stay `submitted`.

`GradeNotification` fires to all members with a submission row.

---

## Notifications

| Event | Notification | Recipient |
|---|---|---|
| Tugas store (individual or group) | `SubmissionNotification` | course dosen |
| Group store (other members added) | `AcademicUpdateNotification("Ditambahkan ke Kelompok", …)` | other group members |
| Grade individual | `GradeNotification($assignment->title, $course->id)` | the student |
| Grade group | `GradeNotification(...)` | all members with a submission |

See [notifications.md](notifications.md).

---

## Mahasiswa grade view

`Mahasiswa\GradeController::index` (`GET /mahasiswa/grades`) lists all of the student's submissions with the assignment + course join. Used in the "Grades" sidebar entry.

`Dosen\GradeController` exports a per-kelas CSV recap:

`GET /dosen/grades/{course}/export` → `Dosen\GradeController::export` streams a CSV with `NIM`, `Nama`, one column per assignment, and a rolling average. Authorized by `CoursePolicy::view`.
