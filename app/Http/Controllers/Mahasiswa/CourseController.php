<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();

        $query = Course::with('dosen')
            ->withCount(['materials', 'assignments', 'students']);

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

        $enrolled_ids = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->pluck('course_id')
            ->toArray();

        return view('mahasiswa.courses.index', compact('available_courses', 'enrolled_ids', 'sort'));
    }

    public function show(Course $course)
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
