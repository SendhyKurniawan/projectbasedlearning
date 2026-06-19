# Jadwal Mahasiswa (Schedule)

## Ringkasan

`GET /mahasiswa/jadwal` → `mahasiswa.schedule.index` — linimasa per-hari konferensi mendatang dan deadline tugas di seluruh mata kuliah yang diikuti.

- **Controller**: `app/Http/Controllers/Mahasiswa/ScheduleController.php`
- **View**: `resources/views/mahasiswa/schedule/index.blade.php`
- **Route**: didaftarkan di `routes/web.php` di bawah grup middleware `auth + role:mahasiswa`:

```php
Route::get('/jadwal', [Mahasiswa\ScheduleController::class, 'index'])->name('schedule.index');
```

---

## Apa yang ditampilkan

- **Konferensi** dengan `status='live'` ATAU `scheduled_at >= hari ini`.
- **Tugas** dengan `deadline >= hari ini` ATAU `deadline IS NULL` (deadline null masuk ke kunci khusus `'no-deadline'`).
- **Jendela**: 14 hari kalender berikutnya selalu dirender, plus hari-hari setelahnya yang masih punya event.

Tidak ada tampilan lampau — begitu konferensi berakhir atau deadline tugas lewat, ia hilang dari jadwal. Pakai `/mahasiswa/grades` atau halaman course-show untuk menemukan item historis.

---

## Pemuatan data

```php
$enrolledCourseIds = $mahasiswa->enrollments()->pluck('courses.id');
```

Ini adalah **pengecualian konvensi**: sebagian besar controller mahasiswa memakai query mentah `DB::table('enrollments')->where(...)` (lihat [database.md](../database.md#enrollments)). `ScheduleController`, `SubmissionController`, dan `DashboardController` memakai relasi Eloquent. Bila Anda mengganti nama kolom di `enrollments`, grep kedua pola.

```php
$conferences = Conference::whereIn('course_id', $enrolledCourseIds)
    ->where(function ($q) {
        $q->where('status', 'live')
          ->orWhere('scheduled_at', '>=', now()->startOfDay());
    })
    ->with(['course', 'dosen'])
    ->orderBy('scheduled_at')
    ->get();

$assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
    ->with('course')
    ->where(function ($q) {
        $q->whereNull('deadline')
          ->orWhere('deadline', '>=', now()->startOfDay());
    })
    ->orderByRaw('deadline IS NULL, deadline ASC')
    ->get();

$submittedAssignmentIds = $assignments->isNotEmpty()
    ? $mahasiswa->submissions()
        ->whereIn('assignment_id', $assignments->pluck('id'))
        ->pluck('assignment_id')
    : collect();
```

`orderByRaw('deadline IS NULL, deadline ASC')` menempatkan tugas berdeadline-null di bawah daftar non-grup.

`$submittedAssignmentIds` adalah koleksi datar ID assignment agar view bisa melakukan cek `.contains()` cepat per baris.

---

## Pengelompokan per hari

```php
$conferencesByDate = $conferences->groupBy(
    fn ($c) => $c->scheduled_at->format('Y-m-d')
);

$assignmentsByDate = $assignments->groupBy(
    fn ($a) => $a->deadline ? $a->deadline->format('Y-m-d') : 'no-deadline'
);

$days = collect();
for ($i = 0; $i < 14; $i++) {
    $days->push(now()->startOfDay()->addDays($i)->format('Y-m-d'));
}
$extraDays = $conferencesByDate->keys()
    ->merge($assignmentsByDate->keys()->filter(fn ($d) => $d !== 'no-deadline'))
    ->filter(fn ($d) => ! $days->contains($d))
    ->sort()
    ->values();
$days = $days->merge($extraDays);
```

Sehingga view mengiterasi satu daftar string tanggal terurut (`Y-m-d`), mencari `$conferencesByDate[$day]` dan `$assignmentsByDate[$day]`. Bucket `'no-deadline'` dirender sebagai panel terpisah di bawah grid per-hari.

---

## Variabel view

| Variabel | Tipe | Isi |
|---|---|---|
| `$days` | `Collection<string>` | string tanggal terurut (jendela 14-hari + ekstra dengan event) |
| `$conferencesByDate` | `Collection<string, Collection<Conference>>` | dikunci `Y-m-d` |
| `$assignmentsByDate` | `Collection<string, Collection<Assignment>>` | dikunci `Y-m-d`; entri deadline-null di kunci `'no-deadline'` |
| `$submittedAssignmentIds` | `Collection<int>` | daftar datar untuk cek `.contains($id)` |

---

## Pewarnaan urgensi (dirender inline di view)

View menghitung urgensi per-tugas dari waktu-ke-deadline:

| Kondisi | Label | Warna |
|---|---|---|
| `now > deadline` | `lewat` | error |
| `0 < jam_tersisa < 48` | `urgent` | error |
| `48 <= jam_tersisa < 168` (7 hari) | `soon` | tertiary |
| `>= 168` | `ok` | netral |

Tugas yang sudah dikumpulkan (`$submittedAssignmentIds->contains($a->id)`) menampilkan judul dicoret dan badge "Sudah Submit" tanpa memandang urgensi.

---

## Konferensi dalam jadwal

Konferensi yang terdaftar mencakup `live` dan terjadwal masa depan. Mahasiswa tak bisa bergabung yang `scheduled` — mengklik "Gabung" hanya berfungsi setelah dosen membaliknya ke `live` via `Dosen\ConferenceController::start`. Lihat [conferences.md](conferences.md).

Tiap kartu konferensi menautkan ke `route('mahasiswa.conferences.room', $conference)` sehingga routing lewat peluncur Jitsi konsisten dengan daftar konferensi per-mata kuliah.

---

## Yang TIDAK disertakan view ini

- Percobaan kuis yang sedang berlangsung (tidak ada timestamp `started_at` yang dimunculkan di sini).
- Sesi pengerjaan exercise.
- Aktivitas diskusi atau pengumuman.
- Item lampau (pakai view nilai).
- Pengingat tingkat-mata-kuliah yang tak terkait tugas/konferensi spesifik.

Bila Anda menambah salah satunya, cerminkan pola bucket-hari agar view tetap satu iterasi atas `$days`.
