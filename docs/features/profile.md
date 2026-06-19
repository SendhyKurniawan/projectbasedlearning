# Profil (Profile)

Pengeditan profil swalayan untuk user yang login. Dibagikan ke semua role. Dirutekan di bawah grup middleware auth-only.

```php
// routes/web.php (grup auth)
Route::get   ('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch ('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
```

Ini salah satu dari hanya dua tempat di aplikasi yang memakai kelas `FormRequest` (satunya `LoginRequest`). Lihat [contributing.md](../contributing.md#validasi--inline-saja) — `$request->validate()` inline adalah aturan di tempat lain.

---

## `ProfileController`

```php
public function edit(Request $request): View
{
    $user = $request->user();
    if ($user->role === 'mahasiswa') {
        $user->load('studentClass.studyProgram.department', 'studentClass.semester.academicYear');
    }

    return view('profile.edit', ['user' => $user]);
}
```

Mahasiswa mendapat eager-loading tambahan konteks akademik penuhnya (jurusan → program studi → kelas → semester → tahun akademik) sehingga halaman profil dapat merender angkatannya tanpa query N+1.

```php
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $request->user()->fill($request->validated());

    if ($request->user()->isDirty('email')) {
        $request->user()->email_verified_at = null;
    }

    $request->user()->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}
```

Perubahan email me-null-kan `email_verified_at`. User perlu verifikasi ulang via alur verifikasi Breeze standar (`verification.notice` / `verification.verify`) — tetapi gerbang `is_active` di `AuthenticatedSessionController::store` **tidak** melihat `email_verified_at`, jadi mengedit email tidak men-logout user atau memblokir login berikutnya.

```php
public function destroy(Request $request): RedirectResponse
{
    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'current_password'],
    ]);

    $user = $request->user();

    Auth::logout();
    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return Redirect::to('/');
}
```

Penghapusan akun memerlukan pengetikan ulang password saat ini (aturan `current_password` mengecek ulang hash bcrypt). Saat sukses: logout, hapus baris, regenerate token sesi, redirect ke `/`.

Penghapusan kaskade yang dipicu oleh penghapusan `User`:
- `enrollments` (cascade)
- `submissions` (cascade)
- `material_views` (cascade)
- `courses` dengan `dosen_id` (cascade — **semua mata kuliahnya hilang**; pertimbangkan mengarsipkan dulu sebelum mengizinkan penghapusan dosen)
- `notifications` (morph polimorfik — tidak ter-cascade; baris yatim di `notifications.notifiable_id`)
- `push_subscriptions` (morph polimorfik — masalah yatim yang sama)
- `discussions`, `discussion_comments` (cascade)

Yatim (`notifications`, `push_subscriptions`) tidak dibersihkan otomatis karena morph tidak punya FK. Mereka tak akan pernah diquery untuk user yang hilang, tetapi menumpuk. Bukan masalah kebenaran, tetapi soal kerapian — pangkas berkala dengan `DELETE FROM notifications WHERE notifiable_id NOT IN (SELECT id FROM users)`.

---

## `ProfileUpdateRequest`

```php
public function rules(): array
{
    return [
        'name'   => ['required', 'string', 'max:255'],
        'email'  => [
            'required', 'string', 'lowercase', 'email', 'max:255',
            Rule::unique(User::class)->ignore($this->user()->id),
        ],
        'nim'    => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
        'nip'    => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
        'sso_id' => ['nullable', 'string', 'max:255'],
    ];
}
```

Field yang dapat diedit:

| Field | Catatan |
|---|---|
| `name` | wajib |
| `email` | wajib, lowercase, harus unik antar user (kecuali diri sendiri) |
| `nim` | opsional, unik antar user (kecuali diri sendiri) — praktiknya mahasiswa saja |
| `nip` | opsional, unik antar user (kecuali diri sendiri) — praktiknya dosen saja |
| `sso_id` | dicadangkan untuk integrasi SSO mendatang; tanpa validasi selain max:255 |

Field yang secara eksplisit **tidak** dapat diedit lewat form ini:

- `role` — hanya admin yang dapat mengubah (`/admin/users/{user}/edit`)
- `is_active` — hanya admin (`/admin/users/{user}/toggle-active`)
- `student_class_id` — hanya admin (`/admin/akademik/classes/{kelas}/students`)
- `password` — lewat `PUT /password` (`PasswordController::update`, alur Breeze)

`mahasiswa` yang tak sengaja mengirim `nip` (atau sebaliknya) akan lolos validasi (`nullable`) dan menimpa kolom. Validasi tidak memberlakukan ID yang sesuai-role; itu konvensi front-end. Bila ingin memberlakukannya sisi-server, tambahkan:

```php
'nim' => [
    'nullable', 'string', 'max:20',
    Rule::requiredIf($this->user()->role === 'mahasiswa'),
    Rule::prohibitedIf($this->user()->role !== 'mahasiswa'),
    Rule::unique(User::class)->ignore($this->user()->id),
],
```

---

## View — `resources/views/profile/edit.blade.php`

Halaman merender tiga bagian (default Breeze yang diadaptasi untuk proyek ini):

1. **Informasi profil** — POST ke `profile.update`. Menampilkan flash sukses via `session('status') === 'profile-updated'`.
2. **Ganti password** — POST ke `password.update` (`PasswordController` terpisah). Memerlukan saat ini + baru + konfirmasi.
3. **Hapus akun** — POST ke `profile.destroy` dengan modal konfirmasi yang meminta password.

Untuk mahasiswa, halaman juga menampilkan konteks akademik yang dimuat (kelas, program studi, jurusan, semester, tahun akademik) secara read-only — berguna sebagai panel "tunjukkan apa yang sistem pikir tentang saya".

---

## Ganti password

`PUT /password` (`Auth\PasswordController::update`, default Breeze):

```php
$validated = $request->validateWithBag('updatePassword', [
    'current_password' => ['required', 'current_password'],
    'password' => ['required', Password::defaults(), 'confirmed'],
]);

$request->user()->update([
    'password' => Hash::make($validated['password']),
]);
```

`Password::defaults()` di Breeze menerapkan aturan password proyek — secara default itu `min(8)` + `mixedCase` + `numbers` + `symbols` + `uncompromised` (cek HaveIBeenPwned). Verifikasi di `app/Providers/AppServiceProvider::boot()` bila Anda perlu melonggarkannya.

---

## Otorisasi

Tidak ada policy pada `ProfileController`. Setiap aksi mengubah baris **user yang sedang login** (`$request->user()`) — tidak ada binding route `{user}`, jadi mutasi lintas-user tidak mungkin. Admin memakai resource `/admin/users/*` terpisah (`Admin\UserController`) untuk mengedit akun orang lain.

| Route | Penjaga |
|---|---|
| `GET /profile` | `auth` |
| `PATCH /profile` | `auth` |
| `DELETE /profile` | `auth` + password saat ini |
| `PUT /password` | `auth` + password saat ini |

Alur OTP (verifikasi registrasi) dan alur reset password terpisah — lihat [auth-roles.md](../auth-roles.md#registrasi--verifikasi-otp).

---

## Terkait: referensi route bersama

| URL | Method | Controller | Tujuan |
|---|---|---|---|
| `/profile` | GET | `ProfileController::edit` | form view + edit |
| `/profile` | PATCH | `ProfileController::update` | ubah name/email/nim/nip/sso_id |
| `/profile` | DELETE | `ProfileController::destroy` | hapus akun sendiri |
| `/password` | PUT | `Auth\PasswordController::update` | ganti password |
| `/confirm-password` | GET/POST | `Auth\ConfirmablePasswordController` | konfirmasi ulang untuk operasi sensitif |
| `/email/verification-notification` | POST | `EmailVerificationNotificationController::store` | kirim ulang email verifikasi (throttle 6/menit) |
| `/verify-email/{id}/{hash}` | GET | `VerifyEmailController` | callback tautan bertanda tangan |
