# Materi (Materials)

## Modelnya

`Material` milik sebuah `Course`. Skema:

| Kolom | Catatan |
|---|---|
| `id` | PK |
| `course_id` | FK courses cascade, terindeks |
| `title` | string |
| `content` | text nullable — sumber Markdown, **bukan** HTML |
| `file_path` | string nullable — lampiran unggahan pada disk `public` di bawah `materials/…` |
| `order` | integer default 0 — reorder drag-and-drop |
| timestamps | |

`Material::views()` adalah `hasMany(MaterialView)`. Helper `hasBeenViewedBy($studentId)` adalah cek keberadaan cepat.

`Course::materials()` mengurutkan berdasarkan `order` menaik.

---

## Route penulisan oleh dosen

```
GET    /dosen/courses/{course}/materials                  materials.index
GET    /dosen/courses/{course}/materials/create           materials.create
POST   /dosen/courses/{course}/materials                  materials.store
POST   /dosen/courses/{course}/materials/reorder          materials.reorder
GET    /dosen/materials/{material}/edit                   materials.edit
PUT    /dosen/materials/{material}                        materials.update
DELETE /dosen/materials/{material}                        materials.destroy
POST   /dosen/materials/{material}/copy                   materials.copy
```

Semua digerbang `CoursePolicy::update` (jadi dosen memiliki mata kuliah, atau admin).

### Create / edit

Form create memakai:

- **EasyMDE** (`markdown-editor.js`) untuk field `content` — menghasilkan Markdown. Disimpan apa adanya di `materials.content`; dirender sisi klien pada view mahasiswa.
- **CodeMirror** inline di dalam EasyMDE untuk blok kode.

Form juga menyertakan `@include('dosen.partials.sibling-kelas-picker')` untuk fan-out saat create. Validasi `store()`:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'content' => 'nullable|string',
    'file' => 'nullable|file|max:20480',          // batas 20 MB
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

Unggahan berkas disimpan di bawah `materials/` pada disk `public`:

```php
$filename = time() . '_' . $file->getClientOriginalName();
$file_path = $file->storeAs('materials', $filename, 'public');
```

`order` materi baru adalah `($course->materials()->max('order') ?? 0) + 1` — disisipkan di akhir.

### Reorder

`POST /dosen/courses/{course}/materials/reorder` menerima array ID terurut:

```php
$request->validate([
    'ordered_ids' => 'required|array',
    'ordered_ids.*' => 'exists:materials,id',
]);

$order = 1;
foreach ($request->ordered_ids as $id) {
    Material::where('id', $id)
            ->where('course_id', $course->id)   // re-scope untuk cegah tulis lintas-course
            ->update(['order' => $order]);
    $order++;
}
```

Drag-and-drop di view daftar materi dosen memicu ini. Handler mengembalikan JSON.

### Update

`PUT /dosen/materials/{material}` menimpa `title`, `content`, dan opsional `file_path`. Bila berkas baru diunggah, `file_path` lama dihapus dulu dari disk `public`.

### Destroy

`DELETE /dosen/materials/{material}` menghapus berkas (bila ada) lalu menghapus baris. Cascade pada `course_id` memastikan tidak ada baris yatim saat mata kuliah dihapus.

### Efek samping notifikasi

`store()`, `update()`, dan store-dengan-fan-out semuanya men-dispatch `AcademicUpdateNotification` ke setiap mahasiswa terdaftar mata kuliah terdampak (dan mahasiswa tiap kelas sibling untuk salinan fan-out). Lihat [notifications.md](notifications.md).

---

## Penanganan berkas dan fan-out

Bila materi punya berkas terlampir (`file_path`), salinan fan-out membuat **salinan terpisah secara fisik** dari berkas untuk tiap kelas sibling:

```
asli (course=42):       materials/1716300000_notes.pdf
sibling kelas=5:        materials/1716300000_notes_kelas5.pdf       (pada create-fanout)
sibling kelas=6:        materials/1716300000_notes_kelas6.pdf
copy nanti ke kelas=7:  materials/1716300000_notes_kelas7_1716300999.pdf   (akhiran _{time()} tambahan)
```

Mengapa? Bila dua materi sibling berbagi path sama, menghapus satu akan `Storage::delete()` berkas yang dipakai yang lain. Akhiran unik mencegah itu.

Saat materi dihapus (`destroy`), hanya `file_path` miliknya yang dilepas tautannya. Berkas asli milik sibling lain tak tersentuh.

Logika copy (`Dosen\MaterialController::copy`):

```php
$ext  = pathinfo($material->file_path, PATHINFO_EXTENSION);
$stem = pathinfo($material->file_path, PATHINFO_FILENAME);
$newPath = 'materials/' . $stem . '_kelas' . $sibling->id . '_' . time() . '.' . $ext;
Storage::disk('public')->copy($material->file_path, $newPath);
```

Irisan keamanan berjalan lebih dulu — hanya sibling yang benar-benar dimiliki dosen yang ditargetkan.

---

## View mahasiswa

`GET /mahasiswa/courses/{course}/materials/{material}` → `Mahasiswa\CourseController::showMaterial`.

Handler:

1. Memverifikasi enrollment via `DB::table('enrollments')`.
2. Memuat materi berdasarkan id (diskop lewat `$course->materials()`).
3. **Mencatat view** via `MaterialView::updateOrCreate(['material_id', 'student_id'], ['viewed_at' => now()])`.
4. Membangun ulang learning path (sama dengan `buildLearningPath()` pada view course-show).
5. Merender `mahasiswa.materials.show` dengan `material`, `learningPath`, `nextItem`, `prevItem`, `currentIndex`.

View memakai `markdown-renderer.js` untuk merender konten Markdown tersimpan sisi klien via marked + highlight.js. Navigasi "next / prev" dihitung sisi server dari indeks learning-path.

---

## `material_views` dan pembukaan prasyarat

```php
MaterialView::updateOrCreate(
    [
        'material_id' => $material->id,
        'student_id'  => $mahasiswa->id,
    ],
    [
        'viewed_at' => now(),
    ]
);
```

Kunci unik `(material_id, student_id)` menjaganya idempoten. Model punya `$timestamps = false` sehingga hanya `viewed_at` yang dilacak.

Keberadaan baris adalah sinyal pembuka untuk `Assignment::isUnlockedFor()`:

```php
public function isUnlockedFor($studentId): bool
{
    if (!$this->required_material_id) {
        return true;
    }

    return MaterialView::where('material_id', $this->required_material_id)
        ->where('student_id', $studentId)
        ->exists();
}
```

Middleware `CheckAssignmentUnlocked` memanggil ini pada route pengumpulan/pengerjaan-exercise — lihat [auth-roles.md](../auth-roles.md). View yang menjadi prasyarat tidak dihapus retroaktif bila mahasiswa sudah membuka materi sebelum dijadikan prasyarat — mahasiswa sudah terbuka aksesnya.
