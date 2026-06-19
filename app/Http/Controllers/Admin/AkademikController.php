<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Controller halaman akademik terpadu (/admin/akademik).
// Menangani seluruh CRUD hierarki akademik dalam satu halaman:
// tahun ajaran → semester → jurusan → prodi → kelas → mata kuliah, plus assign mahasiswa.
class AkademikController extends Controller
{
    // Halaman utama: pilih tahun ajaran/semester/jurusan/prodi lewat query string,
    // lalu tampilkan kelas, mata kuliah (per-semester & per-kelas), dosen, dan kandidat mahasiswa.
    public function index(Request $request)
    {
        $academicYears = AcademicYear::withCount('semesters')->orderByDesc('year_start')->get();

        $selectedAyId  = $request->query('ay');
        $selectedAy    = $selectedAyId
            ? $academicYears->firstWhere('id', $selectedAyId)
            : $academicYears->firstWhere('is_active', true) ?? $academicYears->first();

        $semesters = $selectedAy
            ? Semester::where('academic_year_id', $selectedAy->id)->orderByDesc('is_active')->orderBy('name')->get()
            : collect();

        $selectedSemId = $request->query('sem');
        $selectedSem   = $selectedSemId
            ? $semesters->firstWhere('id', $selectedSemId)
            : $semesters->firstWhere('is_active', true) ?? $semesters->first();

        $departments = Department::withCount('studyPrograms')->orderBy('name')->get();

        $selectedDepId = $request->query('dep');
        $selectedDep   = $selectedDepId
            ? $departments->firstWhere('id', $selectedDepId)
            : $departments->first();

        $studyPrograms = $selectedDep
            ? StudyProgram::where('department_id', $selectedDep->id)->withCount('studentClasses')->orderBy('name')->get()
            : collect();

        $selectedProgId = $request->query('prog');
        $selectedProg   = $selectedProgId
            ? $studyPrograms->firstWhere('id', $selectedProgId)
            : $studyPrograms->first();

        $classes = collect();
        $semesterCourses = collect();
        $classCourses = collect();
        $dosens = User::where('role', 'dosen')->select('id', 'name')->orderBy('name')->get();
        $availableStudents = collect();

        if ($selectedProg && $selectedSem) {
            $classes = StudentClass::where('study_program_id', $selectedProg->id)
                ->where('semester_id', $selectedSem->id)
                ->with(['students:id,name,nim,student_class_id'])
                ->orderBy('name')
                ->get();

            $semesterCourses = Course::where('semester_id', $selectedSem->id)
                ->whereNull('student_class_id')
                ->with('dosen:id,name')
                ->orderBy('nama_matkul')
                ->get();

            $classIds = $classes->pluck('id');
            $classCourses = Course::whereIn('student_class_id', $classIds)
                ->with(['dosen:id,name', 'studentClass:id,name'])
                ->orderBy('nama_matkul')
                ->get();

            $availableStudents = User::where('role', 'mahasiswa')
                ->select('id', 'name', 'nim', 'student_class_id')
                ->orderBy('name')
                ->get();
        }

        return view('admin.akademik.index', compact(
            'academicYears', 'selectedAy',
            'semesters', 'selectedSem',
            'departments', 'selectedDep',
            'studyPrograms', 'selectedProg',
            'classes', 'semesterCourses', 'classCourses',
            'dosens', 'availableStudents'
        ));
    }

    // ===== Tahun Ajaran =====
    // Simpan tahun ajaran baru; bila ditandai aktif, nonaktifkan tahun ajaran lain dulu.
    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'year_start' => 'required|string|max:4',
            'year_end'   => 'required|string|max:4',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $ay = AcademicYear::create($validated);

        return redirect()->route('admin.akademik.index', ['ay' => $ay->id])
            ->with('success', "Tahun Akademik {$ay->year_start}/{$ay->year_end} berhasil ditambahkan.");
    }

    // Perbarui tahun ajaran; jaga agar hanya satu tahun ajaran yang aktif.
    public function updateAcademicYear(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year_start' => 'required|string|max:4',
            'year_end'   => 'required|string|max:4',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            AcademicYear::where('is_active', true)
                ->where('id', '!=', $academicYear->id)
                ->update(['is_active' => false]);
        }

        $academicYear->update($validated);

        return back()->with('success', 'Tahun Akademik berhasil diperbarui.');
    }

    // Hapus tahun ajaran, tetapi tolak jika masih punya semester.
    public function destroyAcademicYear(AcademicYear $academicYear)
    {
        if ($academicYear->semesters()->exists()) {
            return back()->with('error', 'Tahun Akademik tidak dapat dihapus karena masih memiliki semester.');
        }

        $academicYear->delete();

        return redirect()->route('admin.akademik.index')
            ->with('success', 'Tahun Akademik berhasil dihapus.');
    }

    // Jadikan satu tahun ajaran sebagai aktif (nonaktifkan yang lain).
    public function activateAcademicYear(AcademicYear $academicYear)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return back()->with('success', "Tahun Akademik {$academicYear->year_start}/{$academicYear->year_end} diset sebagai aktif.");
    }

    // ===== Semester =====
    // Simpan semester baru (Ganjil/Genap) di bawah sebuah tahun ajaran.
    public function storeSemester(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => ['required', 'string', Rule::in(['Ganjil', 'Genap'])],
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'is_active'        => 'boolean',
        ], [
            'name.in' => 'Semester harus Ganjil atau Genap.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        $sem = Semester::create($validated);

        return redirect()->route('admin.akademik.index', ['ay' => $sem->academic_year_id, 'sem' => $sem->id])
            ->with('success', "Semester {$sem->name} berhasil ditambahkan.");
    }

    // Perbarui semester; jaga agar hanya satu semester yang aktif.
    public function updateSemester(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => ['required', 'string', Rule::in(['Ganjil', 'Genap'])],
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'is_active'        => 'boolean',
        ], [
            'name.in' => 'Semester harus Ganjil atau Genap.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            Semester::where('is_active', true)
                ->where('id', '!=', $semester->id)
                ->update(['is_active' => false]);
        }

        $semester->update($validated);

        return back()->with('success', 'Semester berhasil diperbarui.');
    }

    // Hapus semester, tolak jika masih ada mata kuliah atau kelas yang terkait.
    public function destroySemester(Semester $semester)
    {
        if ($semester->courses()->exists()) {
            return back()->with('error', 'Semester tidak dapat dihapus karena masih memiliki mata kuliah yang terkait.');
        }

        $classCount = StudentClass::where('semester_id', $semester->id)->count();
        if ($classCount > 0) {
            return back()->with('error', "Semester tidak dapat dihapus karena masih ada {$classCount} kelas terkait.");
        }

        $semester->delete();

        return redirect()->route('admin.akademik.index')
            ->with('success', 'Semester berhasil dihapus.');
    }

    // Jadikan satu semester sebagai aktif (nonaktifkan yang lain).
    public function activateSemester(Semester $semester)
    {
        Semester::where('is_active', true)->update(['is_active' => false]);
        $semester->update(['is_active' => true]);

        return back()->with('success', "Semester {$semester->name} diset sebagai aktif.");
    }

    // ===== Jurusan (Department) =====
    // Simpan jurusan baru (kode harus unik).
    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        $dep = Department::create($validated);

        return redirect()->route('admin.akademik.index', ['dep' => $dep->id])
            ->with('success', "Jurusan {$dep->name} berhasil ditambahkan.");
    }

    // Perbarui data jurusan.
    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
        ]);

        $department->update($validated);

        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    // Hapus jurusan, tolak jika masih punya program studi.
    public function destroyDepartment(Department $department)
    {
        if ($department->studyPrograms()->exists()) {
            return back()->with('error', 'Jurusan tidak dapat dihapus karena masih memiliki program studi.');
        }

        $department->delete();

        return redirect()->route('admin.akademik.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }

    // ===== Program Studi (Prodi) =====
    // Simpan prodi baru di bawah sebuah jurusan (kode unik, level D3/D4/S1/S2/S3).
    public function storeStudyProgram(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:study_programs,code',
            'level'         => 'required|in:D3,D4,S1,S2,S3',
        ]);

        $prog = StudyProgram::create($validated);

        return redirect()->route('admin.akademik.index', ['dep' => $prog->department_id, 'prog' => $prog->id])
            ->with('success', "Program Studi {$prog->name} berhasil ditambahkan.");
    }

    // Perbarui data program studi.
    public function updateStudyProgram(Request $request, StudyProgram $studyProgram)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:study_programs,code,' . $studyProgram->id,
            'level'         => 'required|in:D3,D4,S1,S2,S3',
        ]);

        $studyProgram->update($validated);

        return back()->with('success', 'Program Studi berhasil diperbarui.');
    }

    // Hapus prodi, tolak jika masih punya kelas.
    public function destroyStudyProgram(StudyProgram $studyProgram)
    {
        if ($studyProgram->studentClasses()->exists()) {
            return back()->with('error', 'Program Studi tidak dapat dihapus karena masih memiliki kelas.');
        }

        $studyProgram->delete();

        return redirect()->route('admin.akademik.index')
            ->with('success', 'Program Studi berhasil dihapus.');
    }

    // ===== Kelas (Student Class) =====
    // Simpan kelas baru pada kombinasi prodi + semester, lalu arahkan kembali ke konteksnya.
    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'semester_id'      => 'required|exists:semesters,id',
            'name'             => 'required|string|max:255',
        ]);

        $kelas = StudentClass::create($validated);
        $kelas->load('semester.academicYear', 'studyProgram.department');

        $ay = $kelas->semester->academic_year_id ?? null;

        return redirect()->route('admin.akademik.index', [
            'ay'   => $ay,
            'sem'  => $kelas->semester_id,
            'dep'  => $kelas->studyProgram->department_id,
            'prog' => $kelas->study_program_id,
        ])->with('success', "Kelas {$kelas->name} berhasil ditambahkan.");
    }

    // Perbarui data kelas.
    public function updateClass(Request $request, StudentClass $studentClass)
    {
        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'semester_id'      => 'required|exists:semesters,id',
            'name'             => 'required|string|max:255',
        ]);

        $studentClass->update($validated);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    // Hapus kelas, tolak jika masih punya mahasiswa atau mata kuliah khusus kelas.
    public function destroyClass(StudentClass $studentClass)
    {
        if ($studentClass->students()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki mahasiswa terdaftar.');
        }

        if ($studentClass->courses()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki mata kuliah khusus kelas.');
        }

        $studentClass->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    // Assign sejumlah mahasiswa ke sebuah kelas (set kolom student_class_id mereka).
    public function assignStudents(Request $request, StudentClass $studentClass)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $request->student_ids)
            ->where('role', 'mahasiswa')
            ->get();

        if ($users->count() !== count($request->student_ids)) {
            return back()->with('error', 'Beberapa user yang dipilih bukan mahasiswa.');
        }

        User::whereIn('id', $users->pluck('id'))
            ->update(['student_class_id' => $studentClass->id]);

        return back()->with('success', "{$users->count()} mahasiswa berhasil di-assign ke kelas {$studentClass->name}.");
    }

    // Keluarkan seorang mahasiswa dari kelas (kosongkan student_class_id-nya).
    public function unassignStudent(StudentClass $studentClass, User $user)
    {
        if ($user->student_class_id !== $studentClass->id) {
            return back()->with('error', 'Mahasiswa ini tidak terdaftar di kelas tersebut.');
        }

        $user->update(['student_class_id' => null]);

        return back()->with('success', "{$user->name} berhasil dikeluarkan dari kelas.");
    }

    // ===== Mata Kuliah (Course) =====
    // Simpan mata kuliah baru. scope=semester → matkul umum semester (tanpa kelas);
    // scope=class → matkul khusus satu kelas (mengisi student_class_id).
    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'scope'            => 'required|in:semester,class',
            'kode_matkul'      => 'required|string|max:50',
            'nama_matkul'      => 'required|string|max:255',
            'sks'              => 'required|integer|min:1|max:12',
            'dosen_id'         => 'required|exists:users,id',
            'description'      => 'nullable|string',
            'semester_id'      => 'required|exists:semesters,id',
            'student_class_id' => 'nullable|exists:student_classes,id',
        ]);

        $semesterId      = $validated['semester_id'];
        $studentClassId  = $validated['scope'] === 'class' ? ($validated['student_class_id'] ?? null) : null;
        $dosenId         = $validated['dosen_id'];
        $kodeMatkul      = $validated['kode_matkul'];

        // Keunikan bersifat komposit (kode + dosen + semester + kelas), bukan hanya kode_matkul.
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

        if ($exists) {
            return back()->withErrors([
                'kode_matkul' => 'Mata kuliah dengan kode, dosen, dan konteks semester/kelas yang sama sudah ada.',
            ])->withInput();
        }

        Course::create([
            'kode_matkul'      => $kodeMatkul,
            'nama_matkul'      => $validated['nama_matkul'],
            'sks'              => $validated['sks'],
            'dosen_id'         => $dosenId,
            'description'      => $validated['description'] ?? null,
            'semester_id'      => $semesterId,
            'student_class_id' => $studentClassId,
        ]);

        $label = $studentClassId ? 'Mata Kuliah Khusus Kelas' : 'Mata Kuliah Semester';
        return back()->with('success', "{$label} berhasil ditambahkan.");
    }

    // Hapus mata kuliah, tolak jika masih punya materi, tugas, atau enrollment mahasiswa.
    public function destroyCourse(Course $course)
    {
        if ($course->materials()->exists() || $course->assignments()->exists() || $course->students()->exists()) {
            $details = [];
            if ($course->materials()->exists())   $details[] = 'materi';
            if ($course->assignments()->exists()) $details[] = 'tugas';
            if ($course->students()->exists())    $details[] = 'enrollment mahasiswa';

            return back()->with('error',
                "Mata kuliah tidak dapat dihapus karena masih memiliki " . implode(', ', $details) . ". " .
                "Hapus data terkait terlebih dahulu atau hubungi administrator."
            );
        }

        $course->delete();

        return back()->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}
