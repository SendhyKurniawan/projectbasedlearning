# Nilai (Grades)

Tiga controller melayani permukaan "nilai", satu per role. Mereka berbagi tabel dasar (`enrollments`, `assignments`, `submissions`) tetapi merender bentuk berbeda.

| Role | Controller | URL |
|---|---|---|
| Admin | `App\Http\Controllers\Admin\GradeController` | `/admin/grades` |
| Dosen | `App\Http\Controllers\Dosen\GradeController` | `/dosen/grades` |
| Mahasiswa | `App\Http\Controllers\Mahasiswa\GradeController` | `/mahasiswa/grades` |

Alur dosen juga mencakup ekspor CSV dan endpoint quick-grade inline.

---

## Admin — `/admin/grades` (`Admin\GradeController::index`)

View nilai lintas-mata-kuliah read-only untuk pengawasan administratif.

### Filter

| Param query | Efek |
|---|---|
| `search` | menyaring daftar mahasiswa per-mata-kuliah berdasarkan `users.name` LIKE ATAU `users.nim` LIKE |
| `semester_id` | membatasi mata kuliah ke satu semester |
| `academic_year_id` | membatasi mata kuliah via `semester.academic_year_id` |
| `dosen_id` | membatasi mata kuliah ke satu dosen |

### Bentuk query

```php
$coursesQuery = Course::query()
    ->with([
        'semester:id,name,academic_year_id',
        'semester.academicYear:id,year_start,year_end',
        'dosen:id,name',
        'assignments:id,course_id,title,max_score',
    ])
    ->select('id', 'semester_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'created_at');

// + filter di atas

$courses = $coursesQuery->orderBy('created_at', 'desc')->get();

// Mahasiswa per-mata-kuliah dengan filter search opsional, eager-load submissions
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

Variabel view: `$courses`, `$availableSemesters`, `$availableYears`, `$availableDosens`, plus nilai filter. View: `admin.grades.index`.

Admin hanya membaca — tanpa penilaian inline, tanpa ekspor, tanpa drill per-mahasiswa.

---

## Dosen — `/dosen/grades` (`Dosen\GradeController::index`)

Buku nilai untuk mata kuliah milik dosen sendiri. Mendukung drill-down per-kelas dan mengelompokkan mata kuliah sibling berdasarkan `course_group_key`.

### Route

```
GET   /dosen/grades                                         dosen.grades.index
GET   /dosen/grades/{course}/export                         dosen.grades.export
PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade    dosen.grades.quickGrade
```

### Filter

`Dosen\GradeController::index` menerima setiap lapisan hierarki sebagai param query agar dosen dapat drill-down atau melebarkan:

| Param query | Efek |
|---|---|
| `search` | menyaring mahasiswa berdasarkan `name` LIKE ATAU `nim` LIKE |
| `semester_id` | membatasi mata kuliah |
| `academic_year_id` | membatasi via `semester.academic_year_id` |
| `department_id` | membatasi via `studentClass.studyProgram.department_id` |
| `study_program_id` | membatasi via `studentClass.studyProgram_id` |
| `student_class_id` | membatasi ke satu kelas |

### Bentuk query

```php
$coursesQuery = Course::where('dosen_id', $dosenId)
    ->with(['semester.academicYear', 'studentClass.studyProgram.department', 'assignments']);
// + filter

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

// Kelompokkan sibling bersama untuk header matkul
$courseGroups = $courses->groupBy('course_group_key')->map(fn ($g) => [
    'nama_matkul'       => $g->first()->nama_matkul,
    'kode_matkul'       => $g->first()->kode_matkul,
    'semester'          => $g->first()->semester,
    'courses'           => $g->sortBy(fn ($c) => $c->studentClass?->name ?? '')->values(),
    'total_students'    => $g->sum(fn ($c) => $c->students->count()),
    'total_assignments' => $g->sum(fn ($c) => $c->assignments->count()),
])->values();
```

Variabel view: `$courseGroups`, setiap nilai filter, plus daftar dropdown (`$availableYears`, `$availableSemesters`, `$availableDepartments`, `$availablePrograms`, `$availableClasses`). View: `dosen.grades.index`.

### Ekspor CSV

`GET /dosen/grades/{course}/export` → `Dosen\GradeController::export` mengalirkan CSV:

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

Kolom keluaran: `NIM | Nama | <satu kolom per assignment dalam urutan tampilan> | Rata-rata`. Rata-rata adalah `n > 0 ? sum/n : ''` — hanya submission yang dinilai yang berkontribusi. Sel yang belum dinilai dirender kosong, bukan nol.

### Quick-grade (inline)

`PATCH /dosen/grades/{assignment}/{mahasiswa}/quick-grade` menggerakkan edit inline buku nilai:

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

Perilaku kunci:

- `updateOrCreate` berarti baris dibuat bila mahasiswa tak pernah mengumpulkan. Berguna untuk menandai pengumpulan yang terlewat dengan nol atau kredit parsial.
- `submitted_at` diset pada penilaian pertama bila kosong — agar baris tidak permanen ber-timestamp null.
- `group_id` di-hardcode `null` — quick-grade hanya menangani submission individu. Untuk tugas kelompok pakai endpoint `groups.grade` biasa (lihat [assignments.md](assignments.md#penilaian)).
- Mengembalikan JSON untuk konsumen AJAX; tanpa view.

### Notifikasi

Quick-grade **tidak** men-dispatch notifikasi. Jalur penuh `submissions.grade` dan `groups.grade` melakukannya (`GradeNotification`).

---

## Mahasiswa — `/mahasiswa/grades` (`Mahasiswa\GradeController::index`)

View sisi-mahasiswa atas pengumpulan mereka yang dinilai, dikelompokkan per mata kuliah.

### Filter

| Param query | Efek |
|---|---|
| `search` | membatasi mata kuliah berdasarkan `nama_matkul` LIKE ATAU `kode_matkul` LIKE |
| `semester_id` | membatasi mata kuliah |
| `academic_year_id` | membatasi via `semester.academic_year_id` |

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

Relasi `submissions` tiap assignment dibatasi **hanya** ke mahasiswa saat ini — sehingga view dapat merender nilai langsung tanpa penyaringan ekstra.

View: `mahasiswa.grades.index`. Variabel view: `$courses`, `$availableSemesters`, `$availableYears`, `$search`, `$semesterId`, `$academicYearId`.

### Apa yang ditampilkan view

Untuk tiap mata kuliah yang diikuti, semua assignment didaftarkan dengan `score` mahasiswa (atau "—" bila tak ada submission) dan `status`. Dashboard punya histogramnya sendiri (`0–50 | 51–70 | 71–85 | 86–100`) yang dihitung independen oleh `Mahasiswa\DashboardController`.

---

## Ringkasan otorisasi

| Route | Penjaga |
|---|---|
| `/admin/grades` | `auth + role:admin` |
| `/dosen/grades` | `auth + role:dosen` |
| `/dosen/grades/{course}/export` | `auth + role:dosen` + `CoursePolicy::view` (`$course->dosen_id === auth()->id()`) |
| `/dosen/grades/{assignment}/{mahasiswa}/quick-grade` | `auth + role:dosen` + `AssignmentPolicy::update` (mata kuliah assignment dimiliki dosen) |
| `/mahasiswa/grades` | `auth + role:mahasiswa` |

Lihat [auth-roles.md](../auth-roles.md) untuk detail policy.
