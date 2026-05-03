<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke course ini.');
        }
        
        $materials = $course->materials()->orderBy('order')->get();
        
        return view('dosen.materials.index', compact('course', 'materials'));
    }

    public function create(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480', // Max 20MB
        ]);
        
        $data = $request->only(['title', 'content']);
        // Auto-increment order
        $maxOrder = $course->materials()->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;
        $data['course_id'] = $course->id;
        
        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('materials', $filename, 'public');
        }
        
        $material = Material::create($data);
        
        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();
        
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Materi Baru Ditambahkan',
                "Materi baru '{$material->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.materials.show', [$course, $material])
            ));
        }
        
        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    public function edit(Material $material)
    {
        $course = $material->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.materials.edit', compact('material', 'course'));
    }

    public function update(Request $request, Material $material)
    {
        $course = $material->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);
        
        $data = $request->only(['title', 'content']);
        
        // Handle new file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('materials', $filename, 'public');
        }
        
        $material->update($data);
        
        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();
        
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Materi Diperbarui',
                "Materi '{$material->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.",
                route('mahasiswa.materials.show', [$course, $material])
            ));
        }
        
        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy(Material $material)
    {
        $course = $material->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        // Delete file if exists
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        
        $material->delete();
        
        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus!');
    }

    public function reorder(Request $request, Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:materials,id',
        ]);
        
        $order = 1;
        foreach ($request->ordered_ids as $id) {
            Material::where('id', $id)
                    ->where('course_id', $course->id)
                    ->update(['order' => $order]);
            $order++;
        }
        
        return response()->json(['message' => 'Urutan berhasil diperbarui']);
    }
}
