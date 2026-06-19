<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Controller sisi mahasiswa untuk matkul: katalog (terbatas kelas), detail + learning path,
// pendaftaran (enroll), dan tampilan materi (sekaligus mencatat materi dibaca).
class CourseController extends Controller
{
    // Katalog matkul untuk mahasiswa (dengan pencarian & urutan), dibatasi cakupan kelasnya.
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();

        // Matkul yang sudah diikuti — selalu tampil apa pun cakupan kelasnya.
        $enrolled_ids = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->pluck('course_id')
            ->toArray();

        // Batasi katalog ke kelas mahasiswa + matkul umum semester tersebut (plus yang
        // sudah diikuti). student_class_id pada course adalah kelas tempat matkul itu
        // diikat di admin Akademik ("Mata Kuliah Khusus Kelas").
        $classId = $mahasiswa->student_class_id;
        $semesterId = optional($mahasiswa->studentClass)->semester_id;

        $query = Course::with('dosen')
            ->withCount(['materials', 'assignments', 'students'])
            ->where(function ($scope) use ($classId, $semesterId, $enrolled_ids) {
                if ($classId) {
                    $scope->where('student_class_id', $classId);
                }
                $scope->orWhere(function ($wide) use ($semesterId) {
                    $wide->whereNull('student_class_id');
                    if ($semesterId) {
                        $wide->where('semester_id', $semesterId);
                    }
                });
                if (!empty($enrolled_ids)) {
                    $scope->orWhereIn('id', $enrolled_ids);
                }
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_matkul', 'like', "%{$search}%")
                  ->orWhere('kode_matkul', 'like', "%{$search}%")
                  ->orWhereHas('dosen', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sort = $request->input('sort', 'terbaru');
        $available_courses = match ($sort) {
            'terlama' => $query->oldest()->get(),
            'nama_az' => $query->orderBy('nama_matkul', 'asc')->get(),
            'nama_za' => $query->orderBy('nama_matkul', 'desc')->get(),
            default   => $query->latest()->get(),
        };

        return view('mahasiswa.courses.index', compact('available_courses', 'enrolled_ids', 'sort'));
    }

    // Halaman detail matkul + learning path. Wajib sudah enroll untuk mengaksesnya.
    public function show(Course $course)
    {
        $mahasiswa = auth()->user();

        // Pakai query pivot langsung (konvensi flow mahasiswa) untuk cek pendaftaran.
        $is_enrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }

        $course->load([
            'dosen',
            'materials' => fn($q) => $q->orderBy('order'),
            'assignments' => fn($q) => $q->with(['questions', 'requiredMaterial'])->orderBy('order')->orderBy('deadline'),
        ]);

        $materialIds = $course->materials->pluck('id');

        $viewedMaterialIds = MaterialView::where('student_id', $mahasiswa->id)
            ->whereIn('material_id', $materialIds)
            ->pluck('material_id')
            ->toArray();

        $submissions = $mahasiswa->submissions()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with('assignment')
            ->get()
            ->keyBy('assignment_id');

        $allAssignments = $course->assignments;
        $quizzes = $allAssignments->where('type', 'quiz');

        $learningPath = $this->buildLearningPath($course, $viewedMaterialIds, $submissions);

        return view('mahasiswa.courses.show', compact('course', 'learningPath', 'submissions', 'quizzes'));
    }

    // Bangun "jalur belajar": selang-seling materi dan tugas prasyaratnya, sambil
    // menandai item yang sudah selesai dan tugas yang masih terkunci (materi belum dibaca).
    private function buildLearningPath($course, $viewedMaterialIds, $submissions): array
    {
        $path = [];

        $assignmentsByMaterial = $course->assignments->whereNotNull('required_material_id')
            ->keyBy('required_material_id');

        foreach ($course->materials as $material) {
            $path[] = [
                'type' => 'material',
                'item' => $material,
                'completed' => in_array($material->id, $viewedMaterialIds),
                'locked' => false,
            ];

            $relatedAssignment = $assignmentsByMaterial->get($material->id);

            if ($relatedAssignment) {
                $isCompleted = isset($submissions[$relatedAssignment->id]);
                $isUnlocked = in_array($material->id, $viewedMaterialIds);

                $path[] = [
                    'type' => 'assignment',
                    'item' => $relatedAssignment,
                    'completed' => $isCompleted,
                    'locked' => !$isUnlocked,
                ];
            }
        }

        $assignmentsWithoutPrereq = $course->assignments->whereNull('required_material_id');
        foreach ($assignmentsWithoutPrereq as $assignment) {
            $path[] = [
                'type' => 'assignment',
                'item' => $assignment,
                'completed' => isset($submissions[$assignment->id]),
                'locked' => false,
            ];
        }

        return $path;
    }

    // Daftarkan mahasiswa ke matkul (tolak bila sudah terdaftar).
    public function enroll(Request $request, Course $course)
    {
        $mahasiswa = auth()->user();

        $alreadyEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->back()->with('info', 'Anda sudah terdaftar di course ini.');
        }

        $mahasiswa->enrollments()->attach($course->id, [
            'enrolled_at' => now(),
        ]);

        return redirect()->route('mahasiswa.courses.show', $course)
            ->with('success', 'Berhasil mendaftar ke course ' . $course->nama_matkul);
    }

    // Tampilkan satu materi + navigasi prev/next pada learning path; catat materi sebagai dibaca.
    public function showMaterial(Course $course, $materialId)
    {
        $mahasiswa = auth()->user();

        $is_enrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }

        $material = $course->materials()->findOrFail($materialId);

        // Catat/refresh waktu baca materi (jadi syarat membuka tugas prasyaratnya).
        MaterialView::updateOrCreate(
            [
                'material_id' => $material->id,
                'student_id' => $mahasiswa->id,
            ],
            [
                'viewed_at' => now(),
            ]
        );

        $course->load([
            'dosen',
            'materials' => fn($q) => $q->orderBy('order'),
            'assignments' => fn($q) => $q->with('requiredMaterial')->orderBy('order')->orderBy('deadline'),
        ]);

        $materialIds = $course->materials->pluck('id');

        $viewedMaterialIds = MaterialView::where('student_id', $mahasiswa->id)
            ->whereIn('material_id', $materialIds)
            ->pluck('material_id')
            ->toArray();

        $submissions = $mahasiswa->submissions()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with('assignment')
            ->get()
            ->keyBy('assignment_id');

        $learningPath = $this->buildLearningPath($course, $viewedMaterialIds, $submissions);

        $currentIndex = collect($learningPath)->search(
            fn($item) => $item['type'] === 'material' && $item['item']->id === $material->id
        );

        $nextItem = $currentIndex !== false && isset($learningPath[$currentIndex + 1])
            ? $learningPath[$currentIndex + 1]
            : null;

        $prevItem = $currentIndex !== false && $currentIndex > 0
            ? $learningPath[$currentIndex - 1]
            : null;

        return view('mahasiswa.materials.show', compact(
            'course',
            'material',
            'learningPath',
            'nextItem',
            'prevItem',
            'currentIndex'
        ));
    }
}
