# Konferensi (Conferences)

## Modelnya

`Conference` milik sebuah `Course` dan dosen pembuat (User). Kolom:

| Kolom | Catatan |
|---|---|
| `id` | PK |
| `course_id` | FK courses cascade |
| `dosen_id` | FK users cascade — yang membuat/memiliki sesi |
| `title` | string |
| `description` | text nullable |
| `room_name` | string **unik** — dipakai sebagai identifier ruang Jitsi |
| `scheduled_at` | datetime |
| `ended_at` | datetime nullable |
| `status` | enum `scheduled\|live\|ended`, default `scheduled` |
| timestamps | |

`room_name` dibangkitkan sebagai `room-{course_id}-{Str::uuid()}` saat create, sehingga constraint unik global tetap terpenuhi bahkan lintas mata kuliah.

Nilai status persis `scheduled`, `live`, `ended`. Tidak ada `ongoing`. `Conference::isLive()` dan `Conference::isEnded()` adalah helper.

---

## Stack konferensi — Jitsi self-hosted via JWT HS256

Tidak ada LiveKit, tidak ada Jitsi-as-a-Service (JaaS), tidak ada iframe tertanam. Konferensi mengarah ke Jitsi self-hosted di `JITSI_DOMAIN` dan memakai JWT bertanda HS256 yang di-mint oleh `App\Services\JitsiTokenService::mint()`. View ruang merender tombol yang membuka `https://{JITSI_DOMAIN}/{room_name}?jwt={jwt}` di tab baru. Browser menyerahkan JWT ke Jitsi lewat query param standar `?jwt=`.

Ini hasil migrasi terbaru dari JaaS (RSA / 8x8.vc / ruang ber-prefiks tenant) ke self-hosted (HS256 / domain langsung). Lihat riwayat commit (`db5378a refactor(conferences): drop iframe, use standalone Jitsi tab launcher`, `099a07e refactor(conferences): migrate from Jitsi JaaS to self-hosted (HS256 JWT)`).

### `JitsiTokenService::mint($room, $userId, $name, $moderator, $email)`

```php
$now = time();
$payload = [
    'aud' => $appId,                      // JITSI_JWT_APP_ID
    'iss' => $appId,                      // sama
    'sub' => $domain,                     // JITSI_DOMAIN
    'room' => $room,                      // conference.room_name
    'iat' => $now,
    'nbf' => $now - 10,
    'exp' => $now + 7200,                 // 2 jam
    'context' => [
        'user' => [
            'id' => (string) $userId,
            'name' => $name,
            'avatar' => '',
            'email' => $email ?? '',
            'moderator' => $moderator ? 'true' : 'false',
        ],
    ],
];

return JWT::encode($payload, $secret, 'HS256');
```

Bila `JITSI_JWT_APP_ID` atau `JITSI_JWT_APP_SECRET` hilang, `mint()` melempar `RuntimeException('Jitsi JWT credentials not configured…')` — setiap view ruang konferensi lalu akan 500. Munculkan ini di staging sebelum prod.

`JITSI_JWT_APP_ID` dan `JITSI_JWT_APP_SECRET` harus sama dengan `JWT_APP_ID` dan `JWT_APP_SECRET` server Jitsi. Generate secret dengan `openssl rand -hex 32` dan jangan pernah di-commit.

Server Jitsi diprovisioning sesuai [ops/jitsi-self-host.md](../ops/jitsi-self-host.md).

---

## Daur hidup ruang

```
scheduled ──[dosen: start]──► live ──[dosen atau admin: end]──► ended
```

- `scheduled` — dibuat dosen via `conferences.store`. Terlihat di daftar dosen + admin; mahasiswa melihatnya di daftar konferensinya tetapi belum bisa bergabung.
- `live` — diset oleh `Dosen\ConferenceController::start` (`POST /dosen/conferences/{conference}/start`). Mahasiswa kini bisa masuk.
- `ended` — diset oleh `Dosen\ConferenceController::end` atau `Admin\ConferenceController::end`. Juga menyetel `ended_at = now()`. Setelah berakhir, mahasiswa/dosen diarahkan keluar.

Form "end" dosen mengembalikan JSON (bila AJAX/`wantsJson`) atau redirect ke index dengan flash sukses — view ruang memicunya lewat modal konfirmasi Alpine yang mem-POST via form tersembunyi.

---

## Perilaku per role

### Dosen (`Dosen\ConferenceController`)

| Route | Aksi |
|---|---|
| `GET /dosen/courses/{course}/conferences` | Daftar aktif + selesai (`conferences.index`) |
| `GET /dosen/courses/{course}/conferences/create` | `conferences.create` |
| `POST /dosen/courses/{course}/conferences` | `conferences.store` |
| `GET /dosen/conferences/{conference}/edit` | `conferences.edit` |
| `PUT /dosen/conferences/{conference}` | `conferences.update` |
| `DELETE /dosen/conferences/{conference}` | `conferences.destroy` |
| `POST /dosen/conferences/{conference}/copy` | `conferences.copy` |
| `POST /dosen/conferences/{conference}/start` | `conferences.start` (→ status=live) |
| `POST /dosen/conferences/{conference}/end` | `conferences.end` (→ status=ended, ended_at=now) |
| `GET /dosen/conferences/{conference}/room` | mint JWT moderator, render `dosen.conferences.room` |

View ruang merender tautan "Buka Ruang Konferensi" dengan `target="_blank" rel="noopener"` ke `https://{domain}/{room_name}?jwt={jwt}`. JWT di-mint dengan `moderator: true` sehingga dosen mendarat dengan toolbar moderator. Di bawahnya ada tombol "Akhiri Sesi" yang mem-POST ke `conferences.end` setelah konfirmasi.

Otorisasi: `$course->dosen_id === auth()->id()` (cek inline di `authorizeDosen()`).

### Admin (`Admin\ConferenceController`)

| Route | Aksi |
|---|---|
| `GET /admin/conferences` | index pengamat semua konferensi aktif + selesai (berpaginasi) |
| `GET /admin/conferences/{conference}/room` | mint JWT moderator (admin juga moderator), render `admin.conferences.room` |
| `POST /admin/conferences/{conference}/end` | paksa-akhiri sesi |

Admin dinamai `Name (Admin)` di konteks JWT agar dosen/mahasiswa dapat melihat siapa admin di ruang. Aksi `room` admin mewajibkan konferensi `live` (redirect ke index dengan error bila tidak).

### Mahasiswa (`Mahasiswa\ConferenceController`)

| Route | Aksi |
|---|---|
| `GET /mahasiswa/courses/{course}/conferences` | daftar — diurut `live` dulu, lalu `scheduled`, lalu `ended`; diurut `scheduled_at` dalam tiap grup |
| `GET /mahasiswa/conferences/{conference}/room` | mint JWT peserta (`moderator: false`), render `mahasiswa.conferences.room` |

View ruang mahasiswa 403 / redirect bila `!isLive()`. Enrollment dicek via `DB::table('enrollments')`.

---

## View ruang (Blade)

Baik view ruang dosen maupun mahasiswa membangun URL meet secara inline:

```blade
@php
    $domain = config('services.jitsi.domain');
    $meetUrl = 'https://' . $domain . '/' . $conference->room_name . '?jwt=' . $jwt;
@endphp

<a href="{{ $meetUrl }}" target="_blank" rel="noopener">
    Buka Ruang Konferensi
</a>
```

Tautan membuka Jitsi di tab baru, sepenuhnya native (tanpa embedding iframe). Ini menghindari setumpuk masalah permission/Storage/iframe yang mengganggu pendekatan embed. Footer mengingatkan pengguna bahwa klien mobile (aplikasi Jitsi Meet) perlu mengarah ke `JITSI_DOMAIN` di pengaturan aplikasi untuk bergabung.

Tidak ada bundel JavaScript yang diperlukan untuk view ruang — tidak ada `conference-jitsi.js`.

---

## Create / store

Validasi `Dosen\ConferenceController::store`:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'scheduled_at' => 'required|date|after:now',
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

Lalu:

1. Buat konferensi utama di bawah `$course` dengan `room_name` `room-{course.id}-{uuid()}` yang dibangkitkan dan `status=scheduled`.
2. `AcademicUpdateNotification` ke mahasiswa terdaftar `$course` ("Jadwal Kelas Virtual Baru").
3. Untuk tiap sibling valid: buat konferensinya sendiri (uuid baru untuk nama ruang) dan beri tahu mahasiswa sibling.

Irisan keamanan identik dengan jalur fan-out lain — `sibling_ids` yang dikirim difilter lewat `$course->siblings()->pluck('id')`.

---

## Copy

`POST /dosen/conferences/{conference}/copy` menerima `sibling_ids` (required, array, min:1). Setelah irisan:

```php
foreach (Course::whereIn('id', $targetIds)->get() as $sibling) {
    $sibling->conferences()->create([
        'title'        => $conference->title,
        'description'  => $conference->description,
        'scheduled_at' => $conference->scheduled_at,
        'dosen_id'     => auth()->id(),
        'room_name'    => 'room-' . $sibling->id . '-' . Str::uuid(),
        'status'       => 'scheduled',
    ]);
}
```

Tiap salinan mendapat `status=scheduled`, `room_name` segar, dan tanpa `ended_at`. Endpoint copy **tidak** men-dispatch notifikasi (endpoint create yang melakukannya pada jalur per-sibling-nya sendiri).

---

## Update / destroy

`update()` mengizinkan mengubah `title`, `description`, `scheduled_at` saja — bukan `status`. Update memicu `AcademicUpdateNotification` ("Jadwal Kelas Virtual Diperbarui") ke mahasiswa terdaftar.

`destroy()` menghapus baris langsung. Cascade pada `course_id` dan `dosen_id` berlaku saat delete; tidak ada soft-delete per-konferensi.

---

## Mode kegagalan / kasus tepi

- **Variabel env JWT hilang** → `RuntimeException` dari `JitsiTokenService::mint()`, respons 500. Validasi `JITSI_JWT_APP_ID` dan `JITSI_JWT_APP_SECRET` di staging.
- **`JITSI_DOMAIN` salah** → URL meet tetap ter-render, tetapi tab baru gagal terhubung. Verifikasi DNS + Caddy + kontainer web Jitsi.
- **Mahasiswa bergabung konferensi `scheduled`** → controller redirect kembali ke index dengan flash `error`: *"Sesi konferensi ini belum dimulai atau sudah berakhir."*
- **Dosen membuka ulang ruang setelah `end`** → `Dosen\ConferenceController::room` mengecek `isEnded()` dan redirect keluar dengan error.
- **Admin memaksa-akhiri sesi** → `conferences.status='ended'`, `ended_at=now()`. Ruang Jitsi sendiri tidak langsung menendang siapa pun — moderator harus memakai "End Meeting for All" dalam meeting. End sisi-DB adalah tindakan pencatatan.
- **Token ditolak Jitsi** → ketidakcocokan `JITSI_JWT_APP_SECRET` antara aplikasi dan server: Jitsi menampilkan "invalid token" di halaman join. Jalankan langkah verifikasi di [ops/jitsi-self-host.md](../ops/jitsi-self-host.md#langkah-4--verifikasi) untuk memastikan.

---

## Env terkait

| Variabel | Tujuan |
|---|---|
| `JITSI_DOMAIN` | Hostname publik Jitsi self-hosted (mis. `meet.polimedia.pblworkspace.com`). |
| `JITSI_JWT_APP_ID` | Harus sama dengan `JWT_APP_ID` Jitsi. |
| `JITSI_JWT_APP_SECRET` | Harus sama dengan `JWT_APP_SECRET` Jitsi. Hex 32-byte, jangan pernah di-commit. |

Diset di `config/services.php` sebagai `services.jitsi.{domain,jwt_app_id,jwt_app_secret}`.
