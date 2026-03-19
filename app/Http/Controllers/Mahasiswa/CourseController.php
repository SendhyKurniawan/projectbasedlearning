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
    public function index()
    {
        $mahasiswa = auth()->user();

        // Get all available courses with necessary counts in one query
        $available_courses = Course::with('dosen')
            ->withCount(['materials', 'assignments', 'students'])
            ->get();

        // Get enrolled course IDs using a simple pivot query (no eager load needed here)
        $enrolled_ids = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->pluck('course_id')
            ->toArray();

        return view('mahasiswa.courses.index', compact('available_courses', 'enrolled_ids'));
    }

    public function show(Course $course)
    {
        $mahasiswa = auth()->user();

        // Check enrollment using pivot table directly (faster than Eloquent relation query)
        $is_enrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }

        // Load course with all required relationships in a single eager load
        $course->load([
            'dosen',
            'materials' => fn($q) => $q->orderBy('order'),
            'assignments' => fn($q) => $q->with(['questions', 'requiredMaterial'])->orderBy('order')->orderBy('deadline'),
        ]);

        $materialIds = $course->materials->pluck('id');

        // Get material views and submissions in parallel (both with indexed queries)
        $viewedMaterialIds = MaterialView::where('student_id', $mahasiswa->id)
            ->whereIn('material_id', $materialIds)
            ->pluck('material_id')
            ->toArray();

        $submissions = $mahasiswa->submissions()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with('assignment')
            ->get()
            ->keyBy('assignment_id');

        // Filter assignment types from in-memory collection (no extra DB query)
        $allAssignments = $course->assignments;
        $quizzes = $allAssignments->where('type', 'quiz');

        $learningPath = $this->buildLearningPath($course, $viewedMaterialIds, $submissions);

        return view('mahasiswa.courses.show', compact('course', 'learningPath', 'submissions', 'quizzes'));
    }

    private function buildLearningPath($course, $viewedMaterialIds, $submissions): array
    {
        $path = [];

        // Build a lookup map for assignments by required_material_id (no looping search inside loop)
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

        // Add assignments without prerequisites (filtered from in-memory collection)
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

        // Check enrollment using pivot table directly
        $is_enrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }

        $material = $course->materials()->findOrFail($materialId);

        // Track material view (upsert is atomic and efficient)
        MaterialView::updateOrCreate(
            [
                'material_id' => $material->id,
                'student_id' => $mahasiswa->id,
            ],
            [
                'viewed_at' => now(),
            ]
        );

        // Load course relationships for sidebar in a single eager load
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
