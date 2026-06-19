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

// Controller pengelolaan materi oleh dosen: CRUD, atur urutan, dan fan-out/salin ke kelas siblings.
class MaterialController extends Controller
{
    // Daftar materi sebuah matkul (urut 'order') + daftar siblings untuk opsi fan-out.
    public function index(Course $course)
    {
        $this->authorize('update', $course);

        $materials = $course->materials()->orderBy('order')->get();
        $siblings  = $course->siblings();

        return view('dosen.materials.index', compact('course', 'materials', 'siblings'));
    }

    // Form tambah materi (sertakan siblings untuk opsi fan-out).
    public function create(Course $course)
    {
        $this->authorize('update', $course);
        $siblings = $course->siblings();
        return view('dosen.materials.create', compact('course', 'siblings'));
    }

    // Simpan materi baru (opsional unggah berkas), notifikasi mahasiswa, lalu fan-out ke siblings.
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        // Keamanan fan-out: batasi target hanya ke siblings milik matkul ini.
        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids ?? [])
            ->map(fn($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        $baseData = $request->only(['title', 'content']);

        // Simpan berkas unggahan (jika ada) ke disk 'public', nama diberi prefiks timestamp.
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $baseData['file_path'] = $file->storeAs('materials', $filename, 'public');
        }

        // Materi baru ditaruh di urutan terakhir.
        $maxOrder = $course->materials()->max('order') ?? 0;
        $material = Material::create(array_merge($baseData, [
            'course_id' => $course->id,
            'order' => $maxOrder + 1,
        ]));

        $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Materi Baru Ditambahkan',
                "Materi baru '{$material->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.materials.show', [$course, $material])
            ));
        }

        // Fan-out ke tiap kelas sibling terpilih (salin data + berkas + notifikasi).
        $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
        foreach ($targetCourses as $sibling) {
            $sibData = $baseData;
            // Salin berkas secara fisik agar penghapusan di sibling tidak membuat berkas utama hilang.
            if (isset($baseData['file_path'])) {
                $ext = pathinfo($baseData['file_path'], PATHINFO_EXTENSION);
                $stem = pathinfo($baseData['file_path'], PATHINFO_FILENAME);
                $newPath = 'materials/' . $stem . '_kelas' . $sibling->id . '.' . $ext;
                Storage::disk('public')->copy($baseData['file_path'], $newPath);
                $sibData['file_path'] = $newPath;
            }
            $sibOrder = $sibling->materials()->max('order') ?? 0;
            $sibMaterial = Material::create(array_merge($sibData, [
                'course_id' => $sibling->id,
                'order' => $sibOrder + 1,
            ]));
            $sibStudents = User::whereHas('enrollments', fn($q) => $q->where('course_id', $sibling->id))->get();
            if ($sibStudents->isNotEmpty()) {
                Notification::send($sibStudents, new AcademicUpdateNotification(
                    'Materi Baru Ditambahkan',
                    "Materi baru '{$sibMaterial->title}' telah ditambahkan pada mata kuliah {$sibling->nama_matkul}.",
                    route('mahasiswa.materials.show', [$sibling, $sibMaterial])
                ));
            }
        }

        $msg = 'Materi berhasil ditambahkan!';
        if ($targetIds->count()) {
            $msg .= " Disalin ke {$targetIds->count()} kelas lain.";
        }

        return redirect()->route('dosen.materials.index', $course)
            ->with('success', $msg);
    }

    // Form edit materi.
    public function edit(Material $material)
    {
        $course = $material->course;
        $this->authorize('update', $course);

        return view('dosen.materials.edit', compact('material', 'course'));
    }

    // Perbarui materi (ganti berkas bila ada unggahan baru) & beri tahu mahasiswa.
    public function update(Request $request, Material $material)
    {
        $course = $material->course;
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);
        
        $data = $request->only(['title', 'content']);

        // Jika ada berkas baru, hapus berkas lama lalu simpan yang baru.
        if ($request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('materials', $filename, 'public');
        }

        $material->update($data);

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

    // Hapus materi beserta berkasnya (jika ada).
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

    // Salin satu materi ke beberapa kelas siblings (berkas disalin per kelas, suffix unik).
    public function copy(Request $request, Material $material)
    {
        $course = $material->course;
        $this->authorize('update', $course);

        $request->validate([
            'sibling_ids' => 'required|array|min:1',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        // Batasi target ke siblings milik matkul ini saja.
        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids)
            ->map(fn($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        if ($targetIds->isEmpty()) {
            return back()->with('error', 'Pilih kelas tujuan yang valid.');
        }

        $targetCourses = Course::whereIn('id', $targetIds)->get();
        foreach ($targetCourses as $sibling) {
            $sibData = [
                'course_id' => $sibling->id,
                'title'     => $material->title,
                'content'   => $material->content,
                'file_path' => null,
                'order'     => ($sibling->materials()->max('order') ?? 0) + 1,
            ];
            if ($material->file_path) {
                $ext     = pathinfo($material->file_path, PATHINFO_EXTENSION);
                $stem    = pathinfo($material->file_path, PATHINFO_FILENAME);
                $newPath = 'materials/' . $stem . '_kelas' . $sibling->id . '_' . time() . '.' . $ext;
                Storage::disk('public')->copy($material->file_path, $newPath);
                $sibData['file_path'] = $newPath;
            }
            $sibling->materials()->create($sibData);
        }

        return back()->with('success', "Materi '{$material->title}' disalin ke {$targetIds->count()} kelas lain.");
    }

    // Simpan urutan baru materi hasil drag-and-drop (dikirim via AJAX sebagai daftar id terurut).
    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);

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
