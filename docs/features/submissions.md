# Submissions

## Submission Lifecycle

```
submitted ──► graded
    │
    └──► late  (if submitted after deadline — set at submission time)
```

`status` is set to `late` at submission time if `now() > $assignment->deadline`. Dosen can still grade late submissions — status stays `late`, score is added.

---

## Tugas Submission Flow

1. Mahasiswa POSTs to `POST /mahasiswa/submissions` (resource `store`)
2. `Mahasiswa\SubmissionController::store` validates `assignment_id`, `file` (PDF) or `url`
3. `submission_format` on the assignment drives which input is required: `pdf` means file upload, `url` means URL string
4. `Submission` record created with `status = submitted` (or `late`)
5. `NotificationService` fires `sendSubmissionNotification` to dosen

**Group tugas**: if `assignment->is_group`, mahasiswa must be in a `Group` for that assignment before submitting. `GroupController` handles group creation and joining. `Submission->group_id` links to the group; `gradeGroup` on dosen side distributes score across group members.

---

## Quiz Submission Flow

Handled by `Mahasiswa\QuizController`, not `SubmissionController`. See [assignments.md](assignments.md) for the full quiz flow. Key submission behavior:

- Pure MC quiz: `auto_graded = true`, `status = graded`, score written immediately
- Mixed/essay quiz: `auto_graded = false`, `status = submitted`, provisional score only
- A mahasiswa can only submit once — the start action locks the attempt

---

## Exercise Submission Flow

Handled by `Mahasiswa\ExerciseController::submit` (POST `/mahasiswa/exercises/submit`).

1. Validates the submitted code is not empty
2. Runs keyword match against `exercise_config['required_keywords']`
3. Stores the Piston response in `submissions.validation_result` as JSON
4. Sets `auto_graded = false`, `status = submitted`, score = null
5. Dosen grades manually via `Dosen\AssignmentController::grade`

The "Run" button (during solve) is separate — it goes to `POST /execute-code` (the server-side Piston proxy) and does not create a submission record. Only "Submit" creates the record.

---

## `validation_result`

JSON column on `submissions`. Contains the raw Piston API response:

```json
{
  "matched_keywords": ["def", "return"],
  "missing_keywords": ["for"],
  "ran_successfully": true,
  "stdout": "Hello World\n",
  "stderr": ""
}
```

This is a **hint displayed to dosen** as "Validasi Mesin" in `resources/views/dosen/assignments/submissions.blade.php`. It does not affect `score` or `status`. Dosen decide the final grade themselves.

---

## Group Assignments

| Model | Purpose |
|---|---|
| `Group` | One group per assignment per team. Has `created_by_mahasiswa_id` |
| `GroupMember` | Pivot: `group_id`, `mahasiswa_id` |

`Group::hasMember($userId)` checks membership. Only group members can submit for that group. Dosen grade via `gradeGroup` which distributes score to all `GroupMember` records based on the assignment's `grading_mode` (`equal` → all same score, `individual` → separate inputs).
