# Pengumuman (Announcements)

## Apa ini

Channel komunikasi bersama bagi admin dan dosen untuk menyiarkan pesan ke subset pengguna (semua orang, semua dosen, semua mahasiswa, atau pengguna tertentu). Mahasiswa tidak dapat menulis pengumuman. Resource didaftarkan di bawah grup middleware auth-only bersama — tanpa prefiks role di URL.

```php
// routes/web.php (di dalam grup auth-only)
Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);
```

Jadi URL adalah `/announcements`, `/announcements/create`, `/announcements/{announcement}`, dll.

---

## Model — `App\Models\Announcement`

```php
protected $fillable = [
    'user_id',
    'title',
    'content',
    'target_audience',
    'attachment_path',
    'attachment_name',
    'attachment_mime',
];

public function author()      { return $this->belongsTo(User::class, 'user_id'); }
public function targetedUsers() { return $this->belongsToMany(User::class, 'announcement_user'); }

public function hasAttachment(): bool   { return !empty($this->attachment_path); }
public function attachmentIsImage(): bool { return $this->hasAttachment() && str_starts_with((string)$this->attachment_mime, 'image/'); }
public function attachmentIsPdf(): bool   { return $this->hasAttachment() && $this->attachment_mime === 'application/pdf'; }
```

### Skema

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | PK | |
| `user_id` | FK users cascade | penulis |
| `title` | string | |
| `content` | text | |
| `target_audience` | enum | `all\|dosen\|mahasiswa\|specific`, default `all` |
| `attachment_path` | string nullable | path pada disk `public` di bawah `announcements/…` |
| `attachment_name` | string nullable | nama berkas asli |
| `attachment_mime` | string(100) nullable | untuk keputusan render (preview gambar vs tautan PDF) |
| timestamps | | |

Plus pivot `announcement_user` (untuk `target_audience='specific'`):

| Kolom | Catatan |
|---|---|
| `announcement_id` | FK cascade |
| `user_id` | FK cascade |
| Unik `(announcement_id, user_id)` | mencegah duplikat |

---

## Route

| Method + URL | Aksi | Catatan |
|---|---|---|
| `GET /announcements` | `index` | pencarian + filter target, berpaginasi 10/halaman |
| `GET /announcements/create` | `create` | admin atau dosen saja — 403 untuk mahasiswa |
| `POST /announcements` | `store` | penulis + audiens + lampiran opsional |
| `GET /announcements/{announcement}` | `show` | gerbang visibilitas per-role (lihat bawah) |
| `GET /announcements/{announcement}/edit` | `edit` | penulis ATAU admin |
| `PUT /announcements/{announcement}` | `update` | penulis ATAU admin |
| `DELETE /announcements/{announcement}` | `destroy` | penulis ATAU admin |

---

## Rantai filter index

`AnnouncementController::index` membangun query tergantung role peminta:

```php
$query = Announcement::with('author')->latest();

if ($user->role === 'mahasiswa') {
    $query->where(function ($q) use ($user) {
        $q->whereIn('target_audience', ['all', 'mahasiswa'])
          ->orWhereHas('targetedUsers', fn ($subq) => $subq->where('user_id', $user->id));
    });
} elseif ($user->role === 'dosen') {
    $query->where(function ($q) use ($user) {
        $q->whereIn('target_audience', ['all', 'dosen'])
          ->orWhere('user_id', $user->id)
          ->orWhereHas('targetedUsers', fn ($subq) => $subq->where('user_id', $user->id));
    });
}
// admin melihat semuanya (tanpa where tambahan)
```

Filter opsional tambahan:

- `?search=` mencocokkan `title` ATAU `content` dengan `LIKE %…%`
- `?target=all|dosen|mahasiswa|specific` kecocokan persis

Hasil: `paginate(10)->withQueryString()` — filter bertahan lintas tautan paginasi.

---

## Create / store

`create()` mengembalikan opsi spesifik-role:

| Role | Pilihan audiens | Skop picker user |
|---|---|---|
| admin | `all`, `dosen`, `mahasiswa`, `specific` | semua user kecuali diri sendiri |
| dosen | `mahasiswa`, `specific` | mahasiswa saja |
| mahasiswa | abort 403 | — |

### Validasi

```php
$rules = [
    'title' => 'required|string|max:255',
    'content' => 'required|string',
    'target_audience' => 'required|in:all,dosen,mahasiswa,specific',
    'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
];

if ($request->target_audience === 'specific') {
    $rules['specific_users'] = 'required|array';
    $rules['specific_users.*'] = 'exists:users,id';
}

// cek tambahan dosen
if ($user->role === 'dosen' && !in_array($request->target_audience, ['mahasiswa', 'specific'])) {
    abort(403, 'Akses audiens ditolak.');
}
```

Batas lampiran 10 MB, MIME dibatasi ke format gambar umum + PDF. Berkas mendarat di `announcements/{time()}_{name}` pada disk `public`; `attachment_name` menyimpan nama berkas asli untuk tautan unduh; `attachment_mime` menentukan apakah view menampilkan `<img>` atau tautan unduh.

### Dispatch notifikasi

Setelah menyimpan, controller memmaterialisasi daftar penerima dan mengirim `AnnouncementNotification` (channel database saja):

```php
$notifiableUsers = collect();
if ($validated['target_audience'] === 'all') {
    $notifiableUsers = User::where('id', '!=', $user->id)->get();
} elseif ($validated['target_audience'] === 'mahasiswa') {
    $notifiableUsers = User::where('role', 'mahasiswa')->get();
} elseif ($validated['target_audience'] === 'dosen') {
    $notifiableUsers = User::where('role', 'dosen')->where('id', '!=', $user->id)->get();
} elseif ($validated['target_audience'] === 'specific') {
    $notifiableUsers = User::whereIn('id', $validated['specific_users'])->get();
}

if ($notifiableUsers->isNotEmpty()) {
    Notification::send($notifiableUsers, new AnnouncementNotification(
        $announcement->id, $announcement->title, $user->name
    ));
}
```

`AnnouncementNotification` adalah `ShouldQueue` — lihat [notifications.md](notifications.md). Untuk `target_audience='all'` terhadap basis pengguna besar, ini bisa jadi request lambat di bawah default `QUEUE_CONNECTION=sync`. Ganti ke `database` + worker di produksi.

---

## Show

`AnnouncementController::show` memberlakukan cek visibilitas per-role yang mencerminkan filter index:

```php
$isAllowed = false;

if ($user->role === 'admin' || $announcement->user_id === $user->id) {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'all') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'mahasiswa' && $user->role === 'mahasiswa') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'dosen' && $user->role === 'dosen') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'specific') {
    if ($announcement->targetedUsers()->where('user_id', $user->id)->exists()) {
        $isAllowed = true;
    }
}

if (!$isAllowed) {
    abort(403, 'Anda tidak memiliki hak akses untuk melihat pengumuman ini.');
}
```

User yang mendarat di `/announcements/{id}` lewat URL langsung tetapi bukan target mendapat 403, bahkan bila ia bisa membaca judulnya di daftar notifikasi.

---

## Edit / update / destroy

Hanya penulis asli ATAU admin yang dapat mengubah pengumuman:

```php
if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
    abort(403);
}
```

`update()` menangani penggantian lampiran:

- Flag boolean `remove_attachment` → hapus berkas yang ada, null-kan ketiga kolom lampiran.
- Berkas `attachment` baru → hapus berkas yang ada, simpan yang baru.

Untuk `target_audience='specific'` pivot di-`sync()` ke daftar baru. Beralih dari `specific` akan `detach()` semuanya.

`destroy()` menghapus berkas lampiran (bila ada) lalu baris. Cascade pada `user_id` dan pada pivot `announcement_user` menjaganya tetap bersih.

---

## Payload view (notifikasi)

`AnnouncementNotification::toArray()`:

```json
{
  "title": "Pengumuman Baru",
  "message": "{Nama Penulis} membuat pengumuman: {Judul Pengumuman}",
  "url": "/announcements/{id}",
  "type": "announcement"
}
```

`NotificationController::readAndRedirect` (`GET /notifications/{id}/redirect`) menandai terbaca dan mengikuti `url`, yang mendarat di halaman show pengumuman — tempat gerbang visibilitas berjalan sebagai penjaga terakhir.

---

## Yang sengaja tidak ada

- **Tidak ada channel WebPush** untuk pengumuman. Kelas `AnnouncementNotification` hanya mendaftar `['database']` di `via()`. Bila ingin pengiriman push, tambahkan `WebPushChannel::class` dan method `toWebPush()` (cermin `AcademicUpdateNotification`).
- **Tidak ada riwayat edit.** Update menimpa baris di tempat. Bila perlu jejak audit, tambahkan tabel `revisions` atau log event-model.
- **Tidak ada kadaluarsa / publish terjadwal.** Pengumuman muncul seketika. Tambahkan kolom `published_at` atau `expires_at` bila perlu penjadwalan.
