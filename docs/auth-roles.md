# Auth & Roles

## The role enum

`users.role` is a MySQL `enum('mahasiswa','dosen','admin')` column with default `mahasiswa` (set in `0001_01_01_000000_create_users_table.php`). There is no separate `roles` table — the `App\Models\Role` model used to exist and has been removed.

| Value | Route prefix | Dashboard route | Login screen |
|---|---|---|---|
| `admin` | `/admin` | `admin.dashboard` | `/admin/login` (`Admin\Auth\LoginController`) |
| `dosen` | `/dosen` | `dosen.dashboard` | `/login` (Breeze) |
| `mahasiswa` | `/mahasiswa` | `mahasiswa.dashboard` | `/login` (Breeze) |

The root `/` route in `routes/web.php` does the role dispatch:

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

`User` also exposes helpers used widely in policies: `isAdmin()`, `isDosen()`, `isMahasiswa()`, `hasRole($role)`.

---

## Route group middleware stacks

```php
// Shared auth-only:
Route::middleware('auth')->group(...)

// Role-specific:
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(...)
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(...)
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(...)
```

Middleware aliases are registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'role'                       => \App\Http\Middleware\CheckRole::class,
    'check.assignment.unlocked'  => \App\Http\Middleware\CheckAssignmentUnlocked::class,
]);
```

`$middleware->trustProxies(at: '*')` is also set there — required because Caddy on the host VM terminates TLS before forwarding to the in-container Nginx.

---

## `CheckRole` middleware

`app/Http/Middleware/CheckRole.php`

```php
public function handle(Request $request, Closure $next, string $requiredRole): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (!in_array($user->role, ['admin', 'dosen', 'mahasiswa']) || $user->role !== $requiredRole) {
        // Bounce mismatched roles to their own dashboard.
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

Behaviour summary:
- Guest → `route('login')`.
- Authenticated but wrong role → redirect to **their own** dashboard (not 403). A mahasiswa hitting `/dosen/anything` lands on `mahasiswa.dashboard`.
- Authenticated with unknown role string → `route('login')`.

---

## `CheckAssignmentUnlocked` middleware

`app/Http/Middleware/CheckAssignmentUnlocked.php`

Applied to mahasiswa submission and exercise-solve routes:

```php
Route::resource('submissions', Mahasiswa\SubmissionController::class)
    ->except(['index', 'show'])
    ->middleware('check.assignment.unlocked');

Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])
    ->name('exercises.solve')
    ->middleware('check.assignment.unlocked');
```

The middleware pulls the `assignment` route binding and calls `Assignment::isUnlockedFor(auth()->id())`. If the assignment has a `required_material_id` and the student has no `MaterialView` record for that material, the middleware redirects **back** with `error` flash: *"Anda harus membaca materi prerequisite terlebih dahulu untuk mengakses tugas ini."*

`MaterialView` records are created (via `updateOrCreate`) by `Mahasiswa\CourseController::showMaterial`. Viewing a material that later gets set as a prerequisite still counts — the records are never retroactively cleared.

---

## Policies

Two policies exist and are auto-discovered by Laravel via the `Model ↔ ModelPolicy` naming convention. Always call them via `$this->authorize()` in controllers — do not add ad-hoc role checks or use `Gate::check()`.

### `CoursePolicy` (`app/Policies/CoursePolicy.php`)

| Method | Rule |
|---|---|
| `viewAny(User)` | admin OR dosen |
| `view(User, Course)` | admin OR (dosen AND `user.id === course.dosen_id`) |
| `create(User, ?Course)` | admin OR (dosen AND (course is null OR `user.id === course.dosen_id`)) |
| `update(User, Course)` | admin OR (dosen AND ownership) |
| `delete(User, Course)` | admin only |

Mahasiswa access to a course is **not** gated by this policy — controllers do an inline enrollment check via `DB::table('enrollments')->where(...)->exists()`.

### `AssignmentPolicy` (`app/Policies/AssignmentPolicy.php`)

| Method | Rule |
|---|---|
| `viewAny(User)` | admin OR dosen |
| `view(User, Assignment)` | admin OR (dosen AND owns `assignment.course`) |
| `create(User)` | admin OR dosen |
| `update(User, Assignment)` | admin OR (dosen AND owns `assignment.course`) |
| `delete(User, Assignment)` | admin OR (dosen AND owns `assignment.course`) |

`$assignment->course->dosen_id === $user->id` is the ownership check.

### Calling policies

```php
$this->authorize('view',   $course);      // throws AuthorizationException → 403
$this->authorize('update', $assignment);
$this->authorize('create', $course);      // create accepts an optional Course
```

If a new resource needs authorization, add a policy class first — do **not** sprinkle role checks in controllers.

---

## Admin login

Admin has a dedicated login form to keep `/admin` self-contained:

- `GET /admin/login` → `Admin\Auth\LoginController@create` (view `admin.auth.login`)
- `POST /admin/login` → `Admin\Auth\LoginController@store` — calls `LoginRequest::authenticate()`, then verifies `Auth::user()->role === 'admin'`. Non-admin accounts that happen to enter the right credentials are immediately logged out and shown a redirect-back error.

`GET /admin` (without `/login`) redirects to `admin.login`.

The shared Breeze login at `/login` is for dosen and mahasiswa.

---

## Registration & OTP verification

Mahasiswa and dosen self-register at `/register` (`Auth\RegisteredUserController`). Admin accounts are never created via the public form — admins are added by other admins in `/admin/users`.

### Registration flow

```
POST /register (RegisteredUserController@store)
  ├── validate role-specific fields:
  │     role=mahasiswa → require nim (unique), student_class_id (exists)
  │     role=dosen     → require nip (unique)
  ├── create User with is_active=false, otp_code=6-digit, otp_expires_at=now+10m
  ├── fire Illuminate\Auth\Events\Registered
  ├── $user->notify(new OtpVerificationNotification($code))  // mail channel
  ├── session->put('otp_user_id', $user->id)
  └── redirect → route('verification.otp')  + status flash

GET /verify-otp (OtpVerificationController@create)
  └── reads session('otp_user_id') → renders auth.verify-otp with $email

POST /verify-otp (OtpVerificationController@store)
  ├── validate code = digits:6
  ├── reject if otp_expires_at past → "Kode telah kadaluarsa…"
  ├── reject if hash_equals mismatch → "Kode verifikasi tidak valid."
  ├── on success:
  │     mahasiswa: is_active=true, otp_verified_at=now, email_verified_at ?? now,
  │                Auth::login($user), redirect → mahasiswa.dashboard
  │     dosen:     is_active=false (still!), otp_verified_at=now,
  │                redirect → login with "menunggu persetujuan admin" status
  └── session->forget('otp_user_id')

POST /verify-otp/resend (throttle:3,1)
  ├── regenerate otp_code, set otp_expires_at = now+10m
  └── notify($user, OtpVerificationNotification)
```

### Login-time `is_active` gate

`Auth\AuthenticatedSessionController::store` checks `$user->is_active` after `LoginRequest::authenticate()`:

- `is_active=false` and `otp_verified_at IS NULL` → log out, stash `otp_user_id` in session, redirect to `verification.otp` ("Email Anda belum diverifikasi…").
- `is_active=false` and `otp_verified_at` set (dosen waiting admin approval) → log out, redirect to login with `errors.email = "Akun Anda belum diaktifkan. Silakan hubungi admin untuk konfirmasi."`
- `is_active=true` → normal session regenerate + role-based redirect via `match`.

### Admin approval

Admin lists pending dosen at `/admin/users` (the index counts `User::where('role','dosen')->where('is_active', false)`). Activation is `PATCH /admin/users/{id}/toggle-active` (`UserController::toggleActive`) which flips `is_active`.

### Password reset

Standard Breeze `/forgot-password` → `password.email` → mail. The notification is the project's custom `App\Notifications\ResetPasswordNotification` (Indonesian template) overridden in `User::sendPasswordResetNotification()`.

---

## Auth-related notifications

| Notification | Channel | Queue |
|---|---|---|
| `OtpVerificationNotification` | `mail` | `ShouldQueue` (runs sync under default `QUEUE_CONNECTION=sync`) |
| `ResetPasswordNotification` | inherits `Illuminate\Auth\Notifications\ResetPassword` → mail | `ShouldQueue` |

See [features/notifications.md](features/notifications.md) for the full notification catalog and queue semantics.

---

## Auth route reference (`routes/auth.php`)

| Method + URL | Name | Controller |
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
| `POST /verify-otp/resend` (throttle 3/min) | `verification.otp.resend` | `OtpVerificationController@resend` |
| `GET /verify-email` (auth) | `verification.notice` | `EmailVerificationPromptController` |
| `GET /verify-email/{id}/{hash}` (auth, signed, throttle 6/min) | `verification.verify` | `VerifyEmailController` |
| `POST /email/verification-notification` (auth, throttle 6/min) | `verification.send` | `EmailVerificationNotificationController@store` |
| `GET /confirm-password` (auth) | `password.confirm` | `ConfirmablePasswordController@show` |
| `POST /confirm-password` (auth) | — | `ConfirmablePasswordController@store` |
| `PUT /password` (auth) | `password.update` | `PasswordController@update` |
| `POST /logout` (auth) | `logout` | `AuthenticatedSessionController@destroy` |
