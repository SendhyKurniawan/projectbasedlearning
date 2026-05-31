# Grades

Three controllers serve the "grades" surface, one per role. They share the underlying tables (`enrollments`, `assignments`, `submissions`) but render different shapes.

| Role | Controller | URL |
|---|---|---|
| Admin | `App\Http\Controllers\Admin\GradeController` | `/admin/grades` |
| Dosen | `App\Http\Controllers\Dosen\GradeController` | `/dosen/grades` |
| Mahasiswa | `App\Http\Controllers\Mahasiswa\GradeController` | `/mahasiswa/grades` |

The dosen flow also includes a CSV export and an inline quick-grade endpoint.

---

## Admin — `/admin/grades` (`Admin\GradeController::index`)

Read-only cross-course grade view for administrative oversight.

### Filters

| Query param | Effect |
|---|---|
| `search` | filter the per-course student list by `users.name` LIKE OR `users.nim` LIKE |
| `semester_id` | restrict courses to one semester |
| `academic_year_id` | restrict courses via `semester.academic_year_id` |
| `dosen_id` | restrict courses to one dosen |

### Query shape

```php
$coursesQuery = Course::query()
    ->with([
        'semester:id,name,academic_year_id',
        'semester.academicYear:id,year_start,year_end',
        'dosen:id,name',
        'assignments:id,course_id,title,max_score',
    ])
    ->select('id', 'semester_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'created_at');

// + the filters above

$courses = $coursesQuery->orderBy('created_at', 'desc')->get();

// Per-course students with optional search filter, eager-load submissions
$courses->load(['students' => function ($query) use ($search) {
    $query->select('users.id', 'users.name', 'users.nim');
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('nim', 'like', "%{$search}%");
        });
    }
    $query->with(['submissions' => function ($subQuery) {
        $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score');
    }]);
}]);
```

View variables: `$courses`, `$availableSemesters`, `$availableYears`, `$availableDosens`, plus the filter values. View: `admin.grades.index`.

Admin only reads — no inline grading, no exports, no per-student drill.

---

## Dosen — `/dosen/grades` (`Dosen\GradeController::index`)

The grade book for a dosen's own courses. Supports per-kelas drill-down and clusters sibling courses by `course_group_key`.

### Routes

```
GET   /dosen/grades                                         dosen.grades.index
GET   /dosen/grades/{course}/export                         dosen.grades.export
PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade    dosen.grades.quickGrade
```

### Filters

`Dosen\GradeController::index` accepts every layer of the hierarchy as a query param so the dosen can drill down or widen:

| Query param | Effect |
|---|---|
| `search` | filter students by `name` LIKE OR `nim` LIKE |
| `semester_id` | restrict courses |
| `academic_year_id` | restrict via `semester.academic_year_id` |
| `department_id` | restrict via `studentClass.studyProgram.department_id` |
| `study_program_id` | restrict via `studentClass.studyProgram_id` |
| `student_class_id` | restrict to a single kelas |

### Query shape

```php
$coursesQuery = Course::where('dosen_id', $dosenId)
    ->with(['semester.academicYear', 'studentClass.studyProgram.department', 'assignments']);
// + filters

$courses = $coursesQuery->orderBy('created_at', 'desc')->get();

$courses->load(['students' => function ($query) use ($search) {
    if ($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('nim', 'like', "%{$search}%");
    }
    $query->with(['submissions' => function ($subQuery) {
        $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score', 'status');
    }]);
}]);

// Group siblings together for the matkul header
$courseGroups = $courses->groupBy('course_group_key')->map(fn ($g) => [
    'nama_matkul'       => $g->first()->nama_matkul,
    'kode_matkul'       => $g->first()->kode_matkul,
    'semester'          => $g->first()->semester,
    'courses'           => $g->sortBy(fn ($c) => $c->studentClass?->name ?? '')->values(),
    'total_students'    => $g->sum(fn ($c) => $c->students->count()),
    'total_assignments' => $g->sum(fn ($c) => $c->assignments->count()),
])->values();
```

View variables: `$courseGroups`, every filter value, plus the dropdown lists (`$availableYears`, `$availableSemesters`, `$availableDepartments`, `$availablePrograms`, `$availableClasses`). View: `dosen.grades.index`.

### CSV export

`GET /dosen/grades/{course}/export` → `Dosen\GradeController::export` streams a CSV:

```php
$this->authorize('view', $course);

$course->load('studentClass', 'assignments', 'students.submissions');

$filename = 'nilai-' . str_replace('/', '-', $course->kode_matkul)
                    . '-' . ($course->studentClass?->name ?? 'kelas') . '.csv';

return response()->streamDownload(function () use ($course) {
    $out = fopen('php://output', 'w');

    fputcsv($out, array_merge(
        ['NIM', 'Nama'],
        $course->assignments->pluck('title')->all(),
        ['Rata-rata']
    ));

    foreach ($course->students as $s) {
        $row = [$s->nim, $s->name];
        $sum = 0;
        $n   = 0;

        foreach ($course->assignments as $a) {
            $score = $s->submissions->where('assignment_id', $a->id)->first()?->score;
            $row[] = $score ?? '';
            if ($score !== null) {
                $sum += $score;
                $n++;
            }
        }

        $row[] = $n > 0 ? number_format($sum / $n, 2) : '';
        fputcsv($out, $row);
    }

    fclose($out);
}, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
```

Output columns: `NIM | Nama | <one column per assignment in display order> | Rata-rata`. Average is `n > 0 ? sum/n : ''` — only graded submissions contribute. Ungraded cells render as blank, not zero.

### Quick-grade (inline)

`PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade` powers the grade-book inline edit:

```php
$this->authorize('update', $assignment);

$data = $request->validate([
    'score'    => 'required|integer|min:0|max:' . $assignment->max_score,
    'feedback' => 'nullable|string',
]);

$submission = Submission::updateOrCreate(
    [
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $mahasiswa->id,
        'group_id'      => null,
    ],
    [
        'score'    => $data['score'],
        'feedback' => $data['feedback'] ?? null,
        'status'   => 'graded',
    ]
);

if (! $submission->submitted_at) {
    $submission->update(['submitted_at' => now()]);
}

return response()->json([
    'ok'     => true,
    'score'  => $submission->score,
    'status' => $submission->status,
]);
```

Key behaviour:

- `updateOrCreate` means a row is created if the student never submitted. Useful for marking missed submissions with a zero or partial credit.
- `submitted_at` is set on first grade if absent — so the row isn't permanently null-timestamped.
- `group_id` is hardcoded `null` — quick-grade only handles individual submissions. For group tugas use the regular `groups.grade` endpoint (see [assignments.md](assignments.md#grading)).
- Returns JSON for AJAX consumers; no view.

### Notification

Quick-grade does **not** dispatch a notification. The full `submissions.grade` and `groups.grade` paths do (`GradeNotification`).

---

## Mahasiswa — `/mahasiswa/grades` (`Mahasiswa\GradeController::index`)

Student-side view of their own graded submissions, grouped by course.

### Filters

| Query param | Effect |
|---|---|
| `search` | restrict courses by `nama_matkul` LIKE OR `kode_matkul` LIKE |
| `semester_id` | restrict courses |
| `academic_year_id` | restrict via `semester.academic_year_id` |

### Query

```php
$coursesQuery = Course::whereHas('students', function ($query) use ($mahasiswaId) {
    $query->where('mahasiswa_id', $mahasiswaId);
})->with([
    'semester.academicYear',
    'dosen:id,name',
    'assignments.submissions' => function ($query) use ($mahasiswaId) {
        $query->where('mahasiswa_id', $mahasiswaId)
              ->select('id', 'assignment_id', 'mahasiswa_id', 'score', 'status');
    },
]);
```

Each assignment's `submissions` relation is constrained to **only** the current student — so the view can render the score directly without extra filtering.

View: `mahasiswa.grades.index`. View variables: `$courses`, `$availableSemesters`, `$availableYears`, `$search`, `$semesterId`, `$academicYearId`.

### What the view shows

For each enrolled course, all assignments are listed with the student's `score` (or "—" if no submission) and `status`. The dashboard has its own histogram (`0–50 | 51–70 | 71–85 | 86–100`) computed independently by `Mahasiswa\DashboardController`.

---

## Authorization summary

| Route | Guard |
|---|---|
| `/admin/grades` | `auth + role:admin` |
| `/dosen/grades` | `auth + role:dosen` |
| `/dosen/grades/{course}/export` | `auth + role:dosen` + `CoursePolicy::view` (`$course->dosen_id === auth()->id()`) |
| `/dosen/grades/{assignment}/{mahasiswa}/quick-grade` | `auth + role:dosen` + `AssignmentPolicy::update` (assignment's course owned by dosen) |
| `/mahasiswa/grades` | `auth + role:mahasiswa` |

See [auth-roles.md](../auth-roles.md) for policy details.
