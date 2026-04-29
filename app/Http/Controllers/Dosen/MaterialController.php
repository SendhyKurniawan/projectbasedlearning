<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $materials = $course->materials()->orderBy('order')->get();

        return view('dosen.materials.index', compact('course', 'materials'));
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('dosen.materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,txt,png,jpg,jpeg',
        ]);

        $data = $request->only(['title', 'content']);
        $maxOrder = $course->materials()->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;
        $data['course_id'] = $course->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('materials', $filename, 'public');
        }

        $material = Material::create($data);

        $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
        $this->notifications->sendMaterialCreatedNotification($students, $material, $course);

        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    public function edit(Material $material)
    {
        $this->authorize('update', $material->course);

        $course = $material->course;

        return view('dosen.materials.edit', compact('material', 'course'));
    }

    public function update(Request $request, Material $material)
    {
        $course = $material->course;
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,txt,png,jpg,jpeg',
        ]);

        $data = $request->only(['title', 'content']);

        if ($request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('materials', $filename, 'public');
        }

        $material->update($data);

        // Only notify on meaningful change (title or file).
        if ($material->wasChanged(['title', 'file_path'])) {
            $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
            $this->notifications->sendMaterialUpdatedNotification($students, $material, $course);
        }

        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy(Material $material)
    {
        $course = $material->course;
        $this->authorize('update', $course);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('dosen.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus!');
    }

    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => [
                'integer',
                Rule::exists('materials', 'id')->where('course_id', $course->id),
            ],
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
