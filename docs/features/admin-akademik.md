# Admin — Akademik (manajemen hierarki terpadu)

## Apa yang digantikannya

Dulu ada satu CRUD per level (`/admin/academic-years`, `/admin/semesters`, `/admin/departments`, `/admin/study-programs`, `/admin/student-classes`) dan drill-down `/admin/hierarchy/…` terpisah. `HierarchyController` telah **dihapus** dan URL drill-down kini me-redirect permanen:

```php
// routes/web.php
Route::get('/admin/hierarchy/{any?}', fn () => redirect()->route('admin.akademik.index'))
    ->where('any', '.*')->middleware(['auth', 'role:admin']);
```

Pengganti terpadunya adalah `/admin/akademik`, dilayani `Admin\AkademikController`. Route resource per-level lama (`admin.academic-years.*`, `admin.semesters.*`, dst.) masih terdaftar demi kompatibilitas mundur — masih berfungsi untuk hal seperti `Admin\AcademicYearController::index` yang merender daftar datar — tetapi pekerjaan admin baru sebaiknya lewat `/admin/akademik`.

---

## Struktur halaman

`GET /admin/akademik` (`admin.akademik.index`) merender satu halaman dengan seluruh hierarki yang dapat dinavigasi via seleksi query-string:

| Param query | Arti |
|---|---|
| `?ay=<id>` | `AcademicYear` terpilih |
| `?sem=<id>` | `Semester` terpilih (harus milik ay terpilih) |
| `?dep=<id>` | `Department` terpilih |
| `?prog=<id>` | `StudyProgram` terpilih (harus milik dep terpilih) |

`index()` menyelesaikannya berurutan — bila salah satu diabaikan, ia memilih "aktif dulu, lalu pertama" sebagai default. Payload yang dihasilkan ke view:

| Variabel | Isi |
|---|---|
| `$academicYears` | semua tahun dengan `semesters_count` |
| `$selectedAy` | diresolusi oleh `?ay` atau aktif atau pertama |
| `$semesters` | semester dari `$selectedAy`, diurut `is_active DESC, name ASC` |
| `$selectedSem` | diresolusi oleh `?sem` atau aktif atau pertama |
| `$departments` | semua jurusan dengan `study_programs_count` |
| `$selectedDep` | diresolusi oleh `?dep` atau pertama |
| `$studyPrograms` | prodi dari `$selectedDep`, dengan `student_classes_count` |
| `$selectedProg` | diresolusi oleh `?prog` atau pertama |
| `$classes` | kelas dari `$selectedProg` + `$selectedSem`, dengan mahasiswa di-eager-load |
| `$semesterCourses` | mata kuliah dengan `semester_id=$selectedSem.id` DAN `student_class_id IS NULL` (matkul se-semester) |
| `$classCourses` | mata kuliah dengan `student_class_id IN $classes.ids` (matkul per-kelas) |
| `$dosens` | semua user `role=dosen` (untuk dropdown create matkul) |
| `$availableStudents` | semua user `role=mahasiswa` (untuk dropdown assign kelas), hanya terisi saat sebuah kelas dipilih |

Hasilnya satu halaman yang dapat mengedit level mana pun secara inline. Tiap route mutasi di bawah redirect kembali ke `admin.akademik.index` dengan param query relevan dipertahankan agar view pasca-aksi tetap terskop ke seleksi yang sama.

---

## Route

Semua route di bawah `Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')`.

### Tahun akademik

```
POST   /admin/akademik/academic-years              akademik.academic-years.store
PUT    /admin/akademik/academic-years/{ay}         akademik.academic-years.update
DELETE /admin/akademik/academic-years/{ay}         akademik.academic-years.destroy
PATCH  /admin/akademik/academic-years/{ay}/activate akademik.academic-years.activate
```

Validasi: `year_start` dan `year_end` string max 4, `is_active` boolean. Menyetel `is_active=true` menonaktifkan setiap AY lain dalam satu bulk update. `activate` adalah pintasan yang melakukan hal sama tanpa memvalidasi ulang field lain. `destroy` memblokir bila `semesters()->exists()`.

### Semester

```
POST   /admin/akademik/semesters                   akademik.semesters.store
PUT    /admin/akademik/semesters/{sem}             akademik.semesters.update
DELETE /admin/akademik/semesters/{sem}             akademik.semesters.destroy
PATCH  /admin/akademik/semesters/{sem}/activate    akademik.semesters.activate
```

Validasi: `name` harus `Ganjil` atau `Genap` (`Rule::in(['Ganjil','Genap'])`), `start_date` dan `end_date` dengan `after:start_date`, `academic_year_id` harus ada, `is_active` boolean. Seperti AY, `is_active=true` menonaktifkan semua semester lain. `destroy` memblokir bila ada `courses()` ATAU `student_classes` mereferensikan semester.

### Jurusan (Department)

```
POST   /admin/akademik/departments                 akademik.departments.store
PUT    /admin/akademik/departments/{dep}           akademik.departments.update
DELETE /admin/akademik/departments/{dep}           akademik.departments.destroy
```

Validasi: `name` wajib, `code` unik pada `departments.code` (mengabaikan diri sendiri saat update). `destroy` memblokir bila `studyPrograms()->exists()`.

### Program studi (Prodi)

```
POST   /admin/akademik/study-programs              akademik.study-programs.store
PUT    /admin/akademik/study-programs/{prog}       akademik.study-programs.update
DELETE /admin/akademik/study-programs/{prog}       akademik.study-programs.destroy
```

Validasi: `department_id` ada, `name` wajib, `code` unik pada `study_programs.code` (abaikan diri sendiri), `level` di `D3|D4|S1|S2|S3`. `destroy` memblokir bila `studentClasses()->exists()`.

### Kelas (Student class)

```
POST   /admin/akademik/classes                                       akademik.classes.store
PUT    /admin/akademik/classes/{kelas}                               akademik.classes.update
DELETE /admin/akademik/classes/{kelas}                               akademik.classes.destroy
POST   /admin/akademik/classes/{kelas}/students                      akademik.classes.assign-students
DELETE /admin/akademik/classes/{kelas}/students/{user}               akademik.classes.unassign-student
```

Validasi: `study_program_id`, `semester_id`, `name`. `destroy` memblokir bila `students()` ATAU `courses()` mereferensikan kelas.

`assignStudents`:

```php
$request->validate([
    'student_ids' => 'required|array|min:1',
    'student_ids.*' => 'exists:users,id',
]);

// Filter: hanya user dengan role=mahasiswa yang benar-benar dipindah
$users = User::whereIn('id', $request->student_ids)->where('role', 'mahasiswa')->get();

if ($users->count() !== count($request->student_ids)) {
    return back()->with('error', 'Beberapa user yang dipilih bukan mahasiswa.');
}

User::whereIn('id', $users->pluck('id'))
    ->update(['student_class_id' => $studentClass->id]);
```

Perhatikan bulk update ini **memindah** mahasiswa ke kelas (mengubah `users.student_class_id`), bukan sekadar menambah enrollment. Seorang mahasiswa punya tepat satu kelas asal.

`unassignStudent` menyetel `user.student_class_id = null`. Penjaga 404 mengecek `$user->student_class_id === $studentClass->id` dulu.

### Mata kuliah (Course)

```
POST   /admin/akademik/courses              akademik.courses.store
DELETE /admin/akademik/courses/{course}     akademik.courses.destroy
```

CRUD mata kuliah di sini hanya mencakup create + delete; pengeditan penuh terjadi via `admin.courses.*` (resource lama di `/admin/courses`). Handler store menerima field `scope`:

- `scope=semester` — `student_class_id` dipaksa null (matkul se-semester; mahasiswa dari kelas mana pun di semester itu bisa enroll)
- `scope=class` — `student_class_id` diambil dari request

```php
$exists = Course::where('kode_matkul', $kodeMatkul)
    ->where('dosen_id', $dosenId)
    ->where('semester_id', $semesterId)
    ->where(function ($q) use ($studentClassId) {
        if ($studentClassId) {
            $q->where('student_class_id', $studentClassId);
        } else {
            $q->whereNull('student_class_id');
        }
    })->exists();
```

Cek keunikan di sini di tingkat aplikasi, bukan tingkat-DB — ia mencakup komposit empat-kolom (termasuk `dosen_id`), sementara indeks DB `courses_code_semester_class_unique` hanya mencakup `(kode_matkul, semester_id, student_class_id)`. Secara teoretis dua dosen bisa mendaftarkan bentuk mata kuliah yang sama; controller memblokirnya dengan cek eksplisit ini.

`destroy` memblokir bila mata kuliah punya materi, tugas, atau mahasiswa terdaftar — menampilkan yang mana ke operator.

---

## Route admin lama (dipertahankan demi kompatibilitas mundur)

Ini masih ada di bawah `Route::middleware(['auth', 'role:admin'])`:

| Resource | Controller | Catatan |
|---|---|---|
| `admin.academic-years.*` | `Admin\AcademicYearController` | Dipakai menggerakkan halaman daftar AY datar |
| `admin.semesters.*` | `Admin\SemesterController` | daftar datar |
| `admin.departments.*` | `Admin\DepartmentController` | daftar datar |
| `admin.study-programs.*` | `Admin\StudyProgramController` | daftar datar |
| `admin.student-classes.*` | `Admin\StudentClassController` | daftar datar |
| `admin.courses.*` | `Admin\CourseController` | CRUD penuh + endpoint enroll/unenroll |
| `admin.users.*` | `Admin\UserController` | CRUD penuh + `toggleActive`, `bulkDestroy` |

Ini ada terutama untuk URL admin langsung yang belum dicakup halaman akademik terpadu (edit mata kuliah penuh, manajemen user). Mereka memakai komposit `Rule::unique` yang sama untuk tabel `courses`.

---

## User admin

`Admin\UserController` (`/admin/users`) adalah CRUD terpisah:

```
GET    /admin/users                            admin.users.index
GET    /admin/users/create                     admin.users.create
POST   /admin/users                            admin.users.store
GET    /admin/users/{user}/edit                admin.users.edit
PUT    /admin/users/{user}                     admin.users.update
DELETE /admin/users/{user}                     admin.users.destroy
DELETE /admin/users/bulk-destroy               admin.users.bulk-destroy
PATCH  /admin/users/{user}/toggle-active       admin.users.toggle-active
```

Filter di index: `?search=` (mencocokkan `name`/`email`/`nim`/`nip`), `?role=`, `?status=active|inactive` (dipetakan ke `is_active=true|false`). Paginasi 15/halaman.

Index menghitung dosen yang menunggu (`role=dosen && is_active=false`) untuk badge antrean aktivasi. `toggleActive` adalah aksi yang membalik `is_active` untuk dosen terdaftar yang menunggu persetujuan admin — lihat [auth-roles.md](../auth-roles.md#persetujuan-admin).

Validasi di `store`:

```php
$validated = $request->validate([
    'name'     => ['required', 'string', 'max:255'],
    'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'role'     => ['required', 'string', 'in:admin,dosen,mahasiswa'],
    'nim'      => ['nullable', 'string', 'max:20', 'unique:users,nim'],
    'nip'      => ['nullable', 'string', 'max:20', 'unique:users,nip'],
    'student_class_id' => ['nullable', 'exists:student_classes,id'],
    'password' => ['required', 'confirmed', Password::defaults()],
]);

$validated['password']  = Hash::make($validated['password']);
$validated['is_active'] = true;  // user yang dibuat admin langsung aktif, tanpa OTP
```

`update` mengizinkan mengubah role dan kelas; password di-hash hanya bila diberikan. `is_active` di-toggle via checkbox.

---

## Debug push admin

`/admin/debug/push` — lihat [notifications.md](notifications.md#debug-admin--push-manual).

---

## Pengamat konferensi admin

`/admin/conferences/*` — lihat [conferences.md](conferences.md#admin-adminconferencecontroller).
