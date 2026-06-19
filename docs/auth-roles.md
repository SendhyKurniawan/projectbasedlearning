# Autentikasi & Role

## Enum role

`users.role` adalah kolom MySQL `enum('mahasiswa','dosen','admin')` dengan default `mahasiswa` (diset di `0001_01_01_000000_create_users_table.php`). Tidak ada tabel `roles` terpisah — model `App\Models\Role` dulu pernah ada dan sudah dihapus.

| Nilai | Prefix route | Route dashboard | Layar login |
|---|---|---|---|
| `admin` | `/admin` | `admin.dashboard` | `/admin/login` (`Admin\Auth\LoginController`) |
| `dosen` | `/dosen` | `dosen.dashboard` | `/login` (Breeze) |
| `mahasiswa` | `/mahasiswa` | `mahasiswa.dashboard` | `/login` (Breeze) |

Route root `/` di `routes/web.php` melakukan pengalihan berdasarkan role:

```php
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin'     => redirect()->route('admin.dashboard'),
            'dosen'     => redirect()->route('dosen.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default     => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});
```

`User` juga menyediakan helper yang dipakai luas di policy: `isAdmin()`, `isDosen()`, `isMahasiswa()`, `hasRole($role)`.

---

## Tumpukan middleware grup route

```php
// Bersama, auth-only:
Route::middleware('auth')->group(...)

// Spesifik role:
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(...)
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(...)
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(...)
```

Alias middleware didaftarkan di `bootstrap/app.php`:

```php
$middleware->alias([
    'role'                       => \App\Http\Middleware\CheckRole::class,
    'check.assignment.unlocked'  => \App\Http\Middleware\CheckAssignmentUnlocked::class,
]);
```

`$middleware->trustProxies(at: '*')` juga diset di sana — diperlukan karena Caddy pada host VM menerminasi TLS sebelum meneruskan ke Nginx di dalam kontainer.

---

## Middleware `CheckRole`

`app/Http/Middleware/CheckRole.php`

```php
public function handle(Request $request, Closure $next, string $requiredRole): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (!in_array($user->role, ['admin', 'dosen', 'mahasiswa']) || $user->role !== $requiredRole) {
        // Pantulkan role yang tidak cocok ke dashboard mereka sendiri.
        return match ($user->role) {
            'admin'     => redirect()->route('admin.dashboard'),
            'dosen'     => redirect()->route('dosen.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default     => redirect()->route('login'),
        };
    }

    return $next($request);
}
```

Ringkasan perilaku:
- Tamu → `route('login')`.
- Terautentikasi tetapi role salah → redirect ke dashboard **mereka sendiri** (bukan 403). Mahasiswa yang mengakses `/dosen/apa-pun` mendarat di `mahasiswa.dashboard`.
- Terautentikasi dengan string role tak dikenal → `route('login')`.

---

## Middleware `CheckAssignmentUnlocked`

`app/Http/Middleware/CheckAssignmentUnlocked.php`

Diterapkan pada route pengumpulan dan pengerjaan-exercise mahasiswa:

```php
Route::resource('submissions', Mahasiswa\SubmissionController::class)
    ->except(['index', 'show'])
    ->middleware('check.assignment.unlocked');

Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])
    ->name('exercises.solve')
    ->middleware('check.assignment.unlocked');
```

Middleware mengambil binding route `assignment` dan memanggil `Assignment::isUnlockedFor(auth()->id())`. Bila tugas punya `required_material_id` dan mahasiswa tidak punya record `MaterialView` untuk materi itu, middleware me-redirect **kembali** dengan flash `error`: *"Anda harus membaca materi prerequisite terlebih dahulu untuk mengakses tugas ini."*

Record `MaterialView` dibuat (via `updateOrCreate`) oleh `Mahasiswa\CourseController::showMaterial`. Membuka materi yang nantinya dijadikan prasyarat tetap dihitung — record tidak pernah dihapus secara retroaktif.

---

## Policy

Ada dua policy dan otomatis ditemukan Laravel via konvensi penamaan `Model ↔ ModelPolicy`. Selalu panggil via `$this->authorize()` di controller — jangan tambah cek role ad-hoc atau pakai `Gate::check()`.

### `CoursePolicy` (`app/Policies/CoursePolicy.php`)

| Method | Aturan |
|---|---|
| `viewAny(User)` | admin ATAU dosen |
| `view(User, Course)` | admin ATAU (dosen DAN `user.id === course.dosen_id`) |
| `create(User, ?Course)` | admin ATAU (dosen DAN (course null ATAU `user.id === course.dosen_id`)) |
| `update(User, Course)` | admin ATAU (dosen DAN pemilik) |
| `delete(User, Course)` | admin saja |

Akses mahasiswa ke mata kuliah **tidak** digerbang policy ini — controller melakukan cek enrollment inline via `DB::table('enrollments')->where(...)->exists()`.

### `AssignmentPolicy` (`app/Policies/AssignmentPolicy.php`)

| Method | Aturan |
|---|---|
| `viewAny(User)` | admin ATAU dosen |
| `view(User, Assignment)` | admin ATAU (dosen DAN memiliki `assignment.course`) |
| `create(User)` | admin ATAU dosen |
| `update(User, Assignment)` | admin ATAU (dosen DAN memiliki `assignment.course`) |
| `delete(User, Assignment)` | admin ATAU (dosen DAN memiliki `assignment.course`) |

`$assignment->course->dosen_id === $user->id` adalah cek kepemilikan.

### Memanggil policy

```php
$this->authorize('view',   $course);      // melempar AuthorizationException → 403
$this->authorize('update', $assignment);
$this->authorize('create', $course);      // create menerima Course opsional
```

Bila resource baru perlu otorisasi, tambahkan kelas policy lebih dulu — **jangan** menyebar cek role di controller.

---

## Login admin

Admin punya form login khusus agar `/admin` mandiri:

- `GET /admin/login` → `Admin\Auth\LoginController@create` (view `admin.auth.login`)
- `POST /admin/login` → `Admin\Auth\LoginController@store` — memanggil `LoginRequest::authenticate()`, lalu memverifikasi `Auth::user()->role === 'admin'`. Akun non-admin yang kebetulan memasukkan kredensial benar langsung di-logout dan diberi error redirect-back.

`GET /admin` (tanpa `/login`) me-redirect ke `admin.login`.

Login Breeze bersama di `/login` untuk dosen dan mahasiswa.

---

## Registrasi & verifikasi OTP

Mahasiswa dan dosen mendaftar sendiri di `/register` (`Auth\RegisteredUserController`). Akun admin tidak pernah dibuat lewat form publik — admin ditambahkan oleh admin lain di `/admin/users`.

### Alur registrasi

```
POST /register (RegisteredUserController@store)
  ├── validasi field spesifik role:
  │     role=mahasiswa → wajib nim (unik), student_class_id (exists)
  │     role=dosen     → wajib nip (unik)
  ├── buat User dengan is_active=false, otp_code=6-digit, otp_expires_at=now+10m
  ├── fire Illuminate\Auth\Events\Registered
  ├── $user->notify(new OtpVerificationNotification($code))  // channel mail
  ├── session->put('otp_user_id', $user->id)
  └── redirect → route('verification.otp')  + flash status

GET /verify-otp (OtpVerificationController@create)
  └── baca session('otp_user_id') → render auth.verify-otp dengan $email

POST /verify-otp (OtpVerificationController@store)
  ├── validasi code = digits:6
  ├── tolak bila otp_expires_at lewat → "Kode telah kadaluarsa…"
  ├── tolak bila hash_equals tidak cocok → "Kode verifikasi tidak valid."
  ├── saat sukses:
  │     mahasiswa: is_active=true, otp_verified_at=now, email_verified_at ?? now,
  │                Auth::login($user), redirect → mahasiswa.dashboard
  │     dosen:     is_active=false (masih!), otp_verified_at=now,
  │                redirect → login dengan status "menunggu persetujuan admin"
  └── session->forget('otp_user_id')

POST /verify-otp/resend (throttle:3,1)
  ├── generate ulang otp_code, set otp_expires_at = now+10m
  └── notify($user, OtpVerificationNotification)
```

### Gerbang `is_active` saat login

`Auth\AuthenticatedSessionController::store` memeriksa `$user->is_active` setelah `LoginRequest::authenticate()`:

- `is_active=false` dan `otp_verified_at IS NULL` → logout, simpan `otp_user_id` di session, redirect ke `verification.otp` ("Email Anda belum diverifikasi…").
- `is_active=false` dan `otp_verified_at` terisi (dosen menunggu persetujuan admin) → logout, redirect ke login dengan `errors.email = "Akun Anda belum diaktifkan. Silakan hubungi admin untuk konfirmasi."`
- `is_active=true` → regenerate session normal + redirect berbasis role via `match`.

### Persetujuan admin

Admin mendaftar dosen yang menunggu di `/admin/users` (index menghitung `User::where('role','dosen')->where('is_active', false)`). Aktivasi adalah `PATCH /admin/users/{id}/toggle-active` (`UserController::toggleActive`) yang membalik `is_active`.

### Reset password

Standar Breeze `/forgot-password` → `password.email` → mail. Notifikasinya adalah `App\Notifications\ResetPasswordNotification` kustom proyek (template Indonesia) yang di-override di `User::sendPasswordResetNotification()`.

---

## Notifikasi terkait auth

| Notifikasi | Channel | Queue |
|---|---|---|
| `OtpVerificationNotification` | `mail` | `ShouldQueue` (berjalan sync di bawah default `QUEUE_CONNECTION=sync`) |
| `ResetPasswordNotification` | mewarisi `Illuminate\Auth\Notifications\ResetPassword` → mail | `ShouldQueue` |

Lihat [features/notifications.md](features/notifications.md) untuk katalog notifikasi lengkap dan semantik queue.

---

## Referensi route auth (`routes/auth.php`)

| Method + URL | Nama | Controller |
|---|---|---|
| `GET /register` | `register` | `RegisteredUserController@create` |
| `POST /register` | — | `RegisteredUserController@store` |
| `GET /login` | `login` | `AuthenticatedSessionController@create` |
| `POST /login` | — | `AuthenticatedSessionController@store` |
| `GET /forgot-password` | `password.request` | `PasswordResetLinkController@create` |
| `POST /forgot-password` | `password.email` | `PasswordResetLinkController@store` |
| `GET /reset-password/{token}` | `password.reset` | `NewPasswordController@create` |
| `POST /reset-password` | `password.store` | `NewPasswordController@store` |
| `GET /verify-otp` | `verification.otp` | `OtpVerificationController@create` |
| `POST /verify-otp` | `verification.otp.store` | `OtpVerificationController@store` |
| `POST /verify-otp/resend` (throttle 3/menit) | `verification.otp.resend` | `OtpVerificationController@resend` |
| `GET /verify-email` (auth) | `verification.notice` | `EmailVerificationPromptController` |
| `GET /verify-email/{id}/{hash}` (auth, signed, throttle 6/menit) | `verification.verify` | `VerifyEmailController` |
| `POST /email/verification-notification` (auth, throttle 6/menit) | `verification.send` | `EmailVerificationNotificationController@store` |
| `GET /confirm-password` (auth) | `password.confirm` | `ConfirmablePasswordController@show` |
| `POST /confirm-password` (auth) | — | `ConfirmablePasswordController@store` |
| `PUT /password` (auth) | `password.update` | `PasswordController@update` |
| `POST /logout` (auth) | `logout` | `AuthenticatedSessionController@destroy` |
