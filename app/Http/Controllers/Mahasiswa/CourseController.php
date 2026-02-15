<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialView;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user();
        
        // Get all available courses
        $available_courses = Course::with('dosen')
            ->withCount(['materials', 'assignments', 'students'])
            ->get();
        
        // Get enrolled course IDs
        $enrolled_ids = $mahasiswa->enrolledCourses()->pluck('courses.id')->toArray();
        
        return view('mahasiswa.courses.index', compact('available_courses', 'enrolled_ids'));
    }

    public function show(Course $course)
    {
        $mahasiswa = auth()->user();
        
        // Check if student is enrolled
        $is_enrolled = $mahasiswa->enrolledCourses()->where('courses.id', $course->id)->exists();
        
        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }
        
        // Load course with materials and assignments (all types: tugas, quiz, project, exercise)
        $course->load([
            'dosen',
            'materials' => fn($q) => $q->orderBy('order'),
            'assignments' => fn($q) => $q->with(['questions', 'requiredMaterial'])->orderBy('deadline')
        ]);
        
        // Get material views for this student
        $viewedMaterialIds = MaterialView::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('material_id', $course->materials->pluck('id'))
            ->pluck('material_id')
            ->toArray();
        
        // Get student's submissions for this course (includes quizzes now)
        $submissions = $mahasiswa->submissions()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with('assignment')
            ->get()
            ->keyBy('assignment_id');
        
        // Separate assignments into standard learning path and stand-alone quizzes
        // (Maintaining current UX where Quizzes show as a separate section)
        $allAssignments = $course->assignments;
        $learningPathAssignments = $allAssignments->whereIn('type', ['tugas', 'project', 'exercise']);
        $quizzes = $allAssignments->where('type', 'quiz');

        // Build linear learning path using only standard assignments for now
        // Or keep it as is, depends on how buildLearningPath is structured
        $learningPath = $this->buildLearningPath($course, $viewedMaterialIds, $submissions);
        
        return view('mahasiswa.courses.show', compact('course', 'learningPath', 'submissions', 'quizzes'));
    }
    
    private function buildLearningPath($course, $viewedMaterialIds, $submissions)
    {
        $path = [];
        
        foreach ($course->materials as $material) {
            // Add material to path
            $path[] = [
                'type' => 'material',
                'item' => $material,
                'completed' => in_array($material->id, $viewedMaterialIds),
                'locked' => false, // Materials are never locked
            ];
            
            // Find assignment that requires this material
            $relatedAssignment = $course->assignments
                ->where('required_material_id', $material->id)
                ->first();
            
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
        
        // Add assignments without prerequisites at the end
        $assignmentsWithoutPrereq = $course->assignments->whereNull('required_material_id');
        foreach ($assignmentsWithoutPrereq as $assignment) {
            $isCompleted = isset($submissions[$assignment->id]);
            
            $path[] = [
                'type' => 'assignment',
                'item' => $assignment,
                'completed' => $isCompleted,
                'locked' => false, // No prerequisite, always unlocked
            ];
        }
        
        return $path;
    }

    public function enroll(Request $request, Course $course)
    {
        $mahasiswa = auth()->user();
        
        // Check if already enrolled
        if ($mahasiswa->enrolledCourses()->where('courses.id', $course->id)->exists()) {
            return redirect()->back()->with('info', 'Anda sudah terdaftar di course ini.');
        }
        
        // Enroll student
        $mahasiswa->enrolledCourses()->attach($course->id, [
            'enrolled_at' => now()
        ]);
        
        return redirect()->route('mahasiswa.courses.show', $course)
            ->with('success', 'Berhasil mendaftar ke course ' . $course->name);
    }

    public function showMaterial(Course $course, $materialId)
    {
        $mahasiswa = auth()->user();
        
        // Check if student is enrolled
        $is_enrolled = $mahasiswa->enrolledCourses()->where('courses.id', $course->id)->exists();
        
        if (!$is_enrolled) {
            return redirect()->route('mahasiswa.courses.index')
                ->with('error', 'Anda belum terdaftar di course ini.');
        }
        
        // Find material
        $material = $course->materials()->findOrFail($materialId);
        
        // Track material view
        MaterialView::updateOrCreate(
            [
                'material_id' => $material->id,
                'mahasiswa_id' => $mahasiswa->id,
            ],
            [
                'viewed_at' => now(),
            ]
        );
        
        // Load course relationships for sidebar
        $course->load([
            'dosen',
            'materials' => fn($q) => $q->orderBy('order'),
            'assignments' => fn($q) => $q->with('requiredMaterial')->orderBy('deadline')
        ]);
        
        // Get viewed materials and submissions for progress tracking
        $viewedMaterialIds = MaterialView::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('material_id', $course->materials->pluck('id'))
            ->pluck('material_id')
            ->toArray();
        
        $submissions = $mahasiswa->submissions()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with('assignment')
            ->get()
            ->keyBy('assignment_id');
        
        // Build learning path for sidebar
        $learningPath = $this->buildLearningPath($course, $viewedMaterialIds, $submissions);
        
        // Find current position in learning path
        $currentIndex = collect($learningPath)->search(function($item) use ($material) {
            return $item['type'] === 'material' && $item['item']->id === $material->id;
        });
        
        // Get next and previous items
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
