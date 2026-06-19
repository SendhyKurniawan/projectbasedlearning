# Mata Kuliah (Courses)

## Model data

Satu baris `Course` = satu matkul × satu kelas × satu semester × satu dosen. Ini disengaja — setiap kelas mendapat pohon materi/tugas/konferensinya sendiri tanpa berbagi baris antar kelas.

Kolom kunci (lihat [database.md](../database.md#courses) untuk skema lengkap):

- `nama_matkul`, `kode_matkul`, `sks`, `description`, `course_img`
- `dosen_id` (FK users), `semester_id` (FK semesters), `student_class_id` (FK student_classes)
- constraint unik komposit `(kode_matkul, semester_id, student_class_id)` bernama `courses_code_semester_class_unique`. `unique(kode_matkul)` global sebelumnya dihapus oleh migrasi `2026_05_14_000001_relax_course_kode_matkul_unique`.

Relasi (`app/Models/Course.php`):

| Relasi | Mengembalikan |
|---|---|
| `dosen()` | `belongsTo(User)` pada `dosen_id` |
| `semester()` | `belongsTo(Semester)` |
| `studentClass()` | `belongsTo(StudentClass)` pada `student_class_id` |
| `materials()` | `hasMany(Material)->orderBy('order')` |
| `assignments()` | `hasMany(Assignment)->orderBy('order')` |
| `students()` | `belongsToMany(User, 'enrollments', 'course_id', 'mahasiswa_id')->withPivot('final_grade', 'enrolled_at')` |
| `conferences()` | `hasMany(Conference)` |

Sisi-balik `User::courses()` (yang diajar) dan `User::enrollments()` / `User::enrolledCourses()` (terdaftar, dengan alias) didefinisikan di `User`.

---

## Siblings

Dosen yang mengajar `kode_matkul` sama ke dua kelas di semester yang sama mendapat dua baris `Course`. Ini disebut **siblings**.

```php
// Mengembalikan Collection<Course> — tidak termasuk diri sendiri, eager-load studentClass, diurut student_class_id
$siblings = $course->siblings();

// Dipakai mengisi checkbox copy-to-kelas
$siblingIds = $course->siblings()->pluck('id');
```

Pencocokannya adalah `dosen_id + kode_matkul + semester_id`, mengabaikan `student_class_id`. Hasilnya dimemo per instance via `$cachedSiblings` sehingga pemanggilan berulang dalam satu request gratis.

Constraint unik pada `courses` bersifat komposit — seorang dosen *bisa* secara sah memiliki banyak baris dengan `kode_matkul` sama, selama `student_class_id` berbeda. Validasi form admin memberlakukan keunikan komposit ini.

---

## `course_group_key` dan sidebar

Sidebar dan dashboard perlu mengelompokkan mata kuliah sibling secara visual di bawah satu akordeon. Kunci pengelompokan adalah accessor pada model:

```php
// Course::getCourseGroupKeyAttribute()
return $this->dosen_id . '|' . $this->nama_matkul . '|' . ($this->semester_id ?? '');
```

Perhatikan ia memakai **`nama_matkul`** (nama tampilan), bukan `kode_matkul`. Dua mata kuliah dengan nama tampilan sama tetapi kode berbeda (mata kuliah lama yang diganti namanya) akan berkelompok bersama — disengaja.

`SidebarComposer` meng-cache daftar mata kuliah dosen yang sudah diresolusi:

```php
$dosenCourses = Cache::remember("sidebar:dosen:{$user->id}", 300, fn () =>
    Course::where('dosen_id', $user->id)
        ->with('studentClass')
        ->orderBy('created_at', 'desc')
        ->get()
);
$dosenCourseGroups = $dosenCourses->groupBy('course_group_key');
```

Hook `Course::booted()` membersihkan `sidebar:dosen:{dosen_id}` setiap create/update/delete. Bila sidebar tidak menyegarkan setelah perubahan mata kuliah, verifikasi `dosen_id` pada mata kuliah cocok dengan user yang login.

---

## Enrollment

### Sisi admin

`Admin\CourseController` mengekspos CRUD resource penuh di `/admin/courses` dan endpoint enrollment khusus:

| Method + URL | Aksi |
|---|---|
| `POST /admin/courses/{course}/enroll` | `Admin\CourseController@enroll` |
| `DELETE /admin/courses/{course}/enroll/{student}` | `Admin\CourseController@unenroll` |

Admin juga dapat mengelola penugasan ke kelas melalui `/admin/akademik` — lihat `Admin\AkademikController::assignStudents()` dan `unassignStudent()` untuk pergerakan sisi-kelas.

### Self-enrollment mahasiswa

`Mahasiswa\CourseController::enroll` menerima `POST /mahasiswa/courses/{course}/enroll`. Saat ini tidak ada gerbang publik/privat pada mata kuliah — mahasiswa yang login mana pun dapat mendaftar ke mata kuliah mana pun yang bisa ia jangkau via `/mahasiswa/courses`. Bila perlu menggerbang self-enrollment, tambahkan cek di sana.

```php
$alreadyEnrolled = DB::table('enrollments')
    ->where('mahasiswa_id', $mahasiswa->id)
    ->where('course_id', $course->id)
    ->exists();

if ($alreadyEnrolled) {
    return redirect()->back()->with('info', 'Anda sudah terdaftar di course ini.');
}

$mahasiswa->enrollments()->attach($course->id, ['enrolled_at' => now()]);
```

### Membaca enrollment

Dua pola yang berdampingan — keduanya benar, tetapi bila Anda mengubah skema `enrollments`, grep keduanya. Lihat [database.md](../database.md#enrollments).

```php
// Query mentah (dipakai mayoritas controller mahasiswa demi kecepatan)
$isEnrolled = DB::table('enrollments')
    ->where('mahasiswa_id', $mahasiswa->id)
    ->where('course_id', $course->id)
    ->exists();

// Relasi Eloquent (dipakai ScheduleController, SubmissionController, DashboardController)
$courseIds = $mahasiswa->enrollments()->pluck('courses.id');
$isEnrolled = $mahasiswa->enrollments()->where('courses.id', $course->id)->exists();
```

---

## View course-show mahasiswa

`Mahasiswa\CourseController::show` (`GET /mahasiswa/courses/{course}`) membangun "learning path" yang menggabungkan materi dan tugas. View memuat:

- `$course->materials` diurut `order`
- `$course->assignments` dengan `questions` + `requiredMaterial`, diurut `order` lalu `deadline`
- `$viewedMaterialIds` dari `MaterialView`
- `$submissions` dikunci oleh `assignment_id`

Learning path dikonstruksi oleh `buildLearningPath()`:

```
Untuk setiap materi (berurutan):
  emit { type: material, item, completed: viewed, locked: false }
  bila sebuah tugas punya required_material_id === material.id:
    emit { type: assignment, item, completed: has_submission, locked: !viewed }

Setelah semua materi, emit tugas mana pun dengan required_material_id IS NULL.
```

Ini menggerakkan pengalaman UI "selesaikan materi ini untuk membuka tugas berikutnya". Penggerbangan CSS (`locked: true`) murni visual — gerbang sisi server adalah middleware `CheckAssignmentUnlocked` pada route pengumpulan/pengerjaan-exercise.

---

## Copy fan-out

Saat dosen menyalin materi, tugas, konferensi, atau exercise ke kelas sibling, controller mengiriskan `sibling_ids` yang dikirim terhadap sibling sesungguhnya sebelum beraksi:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn($id) => (int) $id)
    ->intersect($allowedSiblingIds);

foreach (Course::whereIn('id', $targetIds)->get() as $sibling) {
    // salin ke $sibling
}
```

Ini adalah batas keamanan — tanpanya, POST yang dibuat-buat bisa menargetkan mata kuliah dosen lain. Komponen Blade `<x-copy-modal>` merender UI checkbox-sibling. Lihat [contributing.md](../contributing.md) untuk aturannya.

| Fitur | Create dengan fan-out | Copy setelah create |
|---|---|---|
| Material | `Dosen\MaterialController::store` | `materials.copy` → `Dosen\MaterialController::copy` |
| Assignment | `Dosen\AssignmentController::store` | `assignments.copy` → `Dosen\AssignmentController::copy` |
| Conference | `Dosen\ConferenceController::store` | `conferences.copy` → `Dosen\ConferenceController::copy` |
| Exercise | `Dosen\ExerciseController::store` | (tidak ada endpoint copy khusus — pakai copy assignment) |

Lihat halaman fitur untuk perilaku fan-out per-tipe ([materials](materials.md), [assignments](assignments.md), [conferences](conferences.md)).

---

## Section dosen dan fallback "URL polos"

Dosen punya beberapa section yang perlu konteks mata kuliah (Materi, Tugas, Exercise, Konferensi). Sidebar menautkannya di bawah mata kuliah pertama dosen. Untuk kenyamanan, URL polos tanpa parameter mata kuliah tersedia:

| Route polos | Perilaku |
|---|---|
| `GET /dosen/materials` (`dosen.materials.bare`) | redirect ke `dosen.materials.index` untuk mata kuliah pertama dosen, atau render view `dosen.no-course` |
| `GET /dosen/assignments` (`dosen.assignments.bare`) | sama, untuk tugas |
| `GET /dosen/exercises` (`dosen.exercises.bare`) | redirect ke index tugas (exercise dan tugas berbagi index) |
| `GET /dosen/conferences` (`dosen.conferences.bare`) | sama, untuk konferensi |

Ini didefinisikan inline di `routes/web.php` dengan closure yang memilih mata kuliah pertama yang dimiliki.

---

## Validasi mata kuliah admin

Admin memberlakukan keunikan komposit — lihat `Admin\CourseController::store/update` dan `Admin\AkademikController::storeCourse`. Saat membuat banyak mata kuliah dengan `kode_matkul` sama untuk kelas berbeda di semester yang sama, validasi harus menerima duplikasi pada `kode_matkul` saja tetapi menolaknya pada tuple penuh.
