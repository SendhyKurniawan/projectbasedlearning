# Berkontribusi

## Model branch

Buat branch dari `main`. Pakai prefiks `feat/`, `fix/`, `chore/`, `refactor/`, `docs/` diikuti deskripsi kebab singkat (mis. `feat/quiz-timer-warning`, `fix/conference-end-redirect`). PR menargetkan `main` langsung. Squash-merge kecuali ada alasan lain.

---

## Sebelum commit

```bash
vendor/bin/pint       # PSR-12 + preset Laravel
composer test         # suite Pest (config:clear → php artisan test)
```

Pint wajib — jalankan setiap save, bukan hanya saat PR. CI akan gagal pada pelanggaran.

Untuk perubahan UI, juga:

```bash
npm run audit:contrast   # bila Anda menyentuh warna design-system.css
npm run build            # pastikan Vite mem-build bersih
```

Lakukan smoke-test manual layar yang berubah di browser sungguhan sebelum membuka PR. Type check dan test memverifikasi kebenaran kode, bukan kebenaran fitur — bila Anda tak bisa menguji UI secara manual, sebutkan di deskripsi PR.

---

## Konvensi kode

### Validasi — inline saja

```php
// Benar — inline di controller
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'deadline' => 'required|date|after:now',
]);

// Salah — tidak ada kelas FormRequest di luar auth Breeze
// Satu-satunya yang ada adalah LoginRequest dan ProfileUpdateRequest.
class StoreAssignmentRequest extends FormRequest { ... }
```

Bila blok validasi yang sama terduplikasi antara `store()` dan `update()`, salin saja. Jangan ekstrak FormRequest hanya demi DRY.

### Otorisasi — Policy, jangan pernah Gate

```php
// Benar
$this->authorize('update', $course);
$this->authorize('view', $assignment);

// Salah
if ($user->role !== 'dosen') abort(403);          // cek role ad-hoc
Gate::check('update-course', $course);            // facade Gate
```

Policy yang ada: `CoursePolicy`, `AssignmentPolicy` (otomatis ditemukan oleh konvensi penamaan Laravel). Bila resource baru perlu auth, tambahkan kelas policy lebih dulu.

Untuk alur sisi mahasiswa yang perlu cek enrollment, lakukan `DB::table('enrollments')->where(...)->exists()` eksplisit inline — itu konvensi saat ini, bukan policy. Lihat [database.md](database.md) untuk daftar pengecualian konvensi.

### Istilah domain Indonesia

Pertahankan apa adanya di kode, identifier, dan dokumen:

`mata_kuliah`, `kode_matkul`, `nama_matkul`, `sks`, `nim`, `nip`, `dosen`, `mahasiswa`, `akademik`, `kelas`, `tugas`, `pilihan_ganda`, `essay`, `code_snippet`.

Ini istilah domain, bukan typo. Jangan ganti ke bahasa Inggris.

### Field form boolean

Form HTML mem-POST `"1"` dan `"0"` sebagai string. Aturan validasi `boolean` menerima itu plus boolean asli (dan `null` saat field tidak dikirim). Pakai itu:

```php
$request->validate([
    'is_group' => 'nullable|boolean',
]);

// Dan cast di model:
protected function casts(): array
{
    return ['is_group' => 'boolean'];
}
```

Sebagian kode lama memakai `'nullable|in:0,1,true,false'` — keduanya berfungsi; pilih `boolean` untuk kode baru.

### Jangan menambah notifikasi tanpa memeriksa setup queue

Setiap kelas notifikasi di `app/Notifications/` mengimplementasikan `ShouldQueue`. Di bawah default `.env.example` (`QUEUE_CONNECTION=sync`) mereka dispatch inline. Di produksi (`QUEUE_CONNECTION=database`) mereka memerlukan queue worker yang berjalan. Saat menambah kelas notifikasi, default ke `ShouldQueue` agar selaras dengan sisanya, dan beri peringatan di deskripsi PR bila notifikasi baru akan fan-out ke banyak penerima (yang membuat jalur kode sinkron menjadi lambat).

---

## Keamanan copy fan-out (jangan dilewati)

Saat dosen menyalin konten (Material, Assignment, Conference, Exercise) ke kelas sibling — baik saat create maupun via endpoint copy — selalu iriskan ID yang dikirim dengan sibling sesungguhnya milik mata kuliah sebelum beraksi:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn($id) => (int) $id)
    ->intersect($allowedSiblingIds);

if ($targetIds->isEmpty()) {
    return back()->with('error', 'Pilih kelas tujuan yang valid.');
}

$targetCourses = Course::whereIn('id', $targetIds)->get();
foreach ($targetCourses as $sibling) {
    // beraksi pada $sibling — jangan pernah pada $request->sibling_ids mentah
}
```

**Jangan pernah melewati irisan ini.** Tanpanya, POST yang dibuat-buat dengan `sibling_ids` sembarang akan menargetkan mata kuliah dosen lain.

Fan-out berkas materi juga menyalin berkas secara fisik dengan akhiran unik (`_kelas{id}` pada create-fanout, `_kelas{id}_{time()}` pada copy berikutnya) untuk menghindari berbagi path. Lihat `Dosen\MaterialController::store()` dan `::copy()` untuk pola persisnya.

Copy kuis membuat assignment **cangkang** tanpa soal. Pesan sukses memberi tahu dosen untuk menambahkan soal ke tiap salinan secara terpisah. Jangan ubah ini — set soal sering perlu penyesuaian per kelas.

---

## Menambah tipe assignment baru

Bila Anda menambah nilai keempat `assignment.type` di samping `tugas | quiz | exercise`:

1. **Migrasi** — perluas enum: `Schema::table('assignments', fn ($t) => $t->enum('type', ['tugas','quiz','exercise','new'])->change())`. Pakai `change()` Laravel (yang memerlukan `doctrine/dbal` di Laravel lama; 12.x hadir dengan dukungan native).
2. **Model `Assignment`** — perbarui PHPDoc tipe dan helper apa pun.
3. **`Dosen\AssignmentController`** — perluas validasi `store()` dan percabangan per-tipe. Putuskan apakah tipe baru perlu `submission_format`, `duration_minutes`, `is_group`, dst.
4. **`Dosen\AssignmentController::copy()`** — pastikan `$assignment->only([…])` menyertakan kolom baru.
5. **Partial view** — tambahkan partial di bawah `resources/views/dosen/assignments/` untuk field form tipe tersebut.
6. **Alur pengumpulan mahasiswa** — tambahkan route + controller. Putuskan apakah `CheckAssignmentUnlocked` harus menggerbangnya (seharusnya ya — terapkan middleware `check.assignment.unlocked`).
7. **Fan-out sibling** — pastikan `siblings()` dan irisan keamanan tetap berlaku.
8. **Seeder** — tambahkan minimal satu contoh di `AssignmentSeeder` agar dev punya data uji.

---

## Menambah logika enrollment

Enrollment mahasiswa memakai query mentah `DB::table('enrollments')->where(...)` di seluruh controller `Mahasiswa\*` (pengecualian: `ScheduleController`, `SubmissionController`, `DashboardController` memakai relasi `User::enrollments()`). Bila Anda mengubah skema `enrollments`:

```bash
grep -r "DB::table('enrollments'" app/
grep -r "->enrollments()"          app/
grep -r "->enrolledCourses()"      app/   # relasi alias yang didefinisikan di User
```

Perbarui setiap lokasi.

---

## Livewire vs. Blade

Ada satu komponen Livewire nyata: `App\Livewire\Discussion\Show`. Ia ada agar pengiriman komentar dapat me-render ulang daftar komentar tanpa reload halaman penuh — Alpine saja tidak bisa.

**Jangan langsung memakai Livewire untuk fitur baru.** Bila Anda butuh:

- interaktivitas murni sisi klien → Alpine
- state sisi server dengan redirect → form Blade biasa + redirect
- render ulang reaktif parsial → baru pertimbangkan Livewire

Livewire 4 punya kuirk yang diketahui: `redirect()` ke URL yang sama tidak akan menyegarkan konten Blade di luar komponen — bila menemui ini, pakai `loadX()` untuk menyegarkan dalam-komponen atau `redirect(..., navigate: false)`.

Kuirk Playwright + Livewire `wire:model`: `.fill()` melompati urutan event input dan men-submit state kosong. Pakai pengetikan lambat `.pressSequentially(text, { delay: 30 })` sebagai gantinya.

---

## Bekerja di repo ini

- Beroperasi dari `D:\Projects\pjbl\`. Abaikan `.claude/worktrees/*`.
- Jangan pakai pemulihan git destruktif (`git reset --hard`, `git checkout .`, `git clean -f`) untuk memperbaiki keadaan rusak. Jelaskan situasinya dan tanya dulu.
- Jangan menghapus istilah Indonesia.
- Pesan commit memakai prefiks ala Conventional Commits: `feat(area): …`, `fix(area): …`, `refactor(area): …`, `chore(area): …`, `docs(area): …`. Lihat `git log` untuk contoh.
- Jangan menambah baris `Co-Authored-By: Claude` atau "🤖 Generated with Claude Code" ke commit/PR — hapus bila muncul.

---

## Template deskripsi PR

```markdown
## Ringkasan
- deskripsi satu baris tentang apa yang berubah
- poin kedua untuk alasannya
- poin ketiga untuk keputusan penting apa pun

## Rencana uji
- [ ] Pest lolos (`composer test`)
- [ ] Pint bersih (`vendor/bin/pint --test`)
- [ ] Sudah menelusuri <fitur> secara manual sebagai <role>
- [ ] (bila ada perubahan env) `.env.example` diperbarui
- [ ] (bila ada perubahan skema) `docs/database.md` diperbarui
- [ ] (bila ada entry Vite ditambahkan) catatan tentang menghapus volume `pjbl_app_build` saat deploy
```
