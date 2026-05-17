# Auth & Roles

## The Role Enum

`users.role` is a string column with three valid values:

| Value | Route prefix | Dashboard |
|---|---|---|
| `admin` | `/admin` | `admin.dashboard` |
| `dosen` | `/dosen` | `dosen.dashboard` |
| `mahasiswa` | `/mahasiswa` | `mahasiswa.dashboard` |

The root `/` redirects to the correct dashboard immediately. There's no shared home page.

---

## Route Group Middleware Stacks

```php
// Shared auth-only (all roles):
Route::middleware('auth')->group(...)

// Role-specific:
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(...)
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(...)
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(...)
```

The `role:` middleware alias is registered in `bootstrap/app.php` pointing to `App\Http\Middleware\CheckRole`.

---

## `CheckRole` Middleware

`app/Http/Middleware/CheckRole.php`

Reads the `role` value from the middleware parameter, compares against `auth()->user()->role`. On mismatch, redirects to the correct dashboard for the user's actual role. On guest, redirects to `login`.

The check happens after Laravel's `auth` guard, so `auth()->user()` is guaranteed to be set when `CheckRole` runs.

---

## `CheckAssignmentUnlocked` Middleware

`app/Http/Middleware/CheckAssignmentUnlocked.php`

Applied to mahasiswa submission and exercise routes:

```php
Route::resource('submissions', ...)->middleware('check.assignment.unlocked');
Route::get('/exercises/{assignment}/solve', ...)->middleware('check.assignment.unlocked');
```

The middleware resolves the `assignment` route binding, then calls `$assignment->isUnlockedFor(auth()->id())`. If the assignment has a `required_material_id` and the student hasn't viewed that material yet, it returns a 403. Viewing a material creates a `MaterialView` record — that's the signal this middleware checks.

---

## Policies

Two policies exist. Always use `$this->authorize()` in controllers — don't add role checks directly or use Gate::check.

### `CoursePolicy` (`app/Policies/CoursePolicy.php`)

Controls dosen access to their own courses. Key methods:

| Method | Guard |
|---|---|
| `view` | `$course->dosen_id === $user->id` |
| `update` | Same |
| `delete` | Same |

Mahasiswa access to courses is via enrollment check, not policy — done inline in `Mahasiswa\CourseController`.

### `AssignmentPolicy` (`app/Policies/AssignmentPolicy.php`)

Controls dosen access to assignments. Key method:

| Method | Guard |
|---|---|
| `view` | Assignment's course belongs to the dosen |
| `update` | Same |
| `delete` | Same |

### Using Policies

```php
// In controller — throws AuthorizationException (403) on failure
$this->authorize('view', $course);
$this->authorize('update', $assignment);

// Both policies are auto-discovered via naming convention
// CoursePolicy ↔ Course model, AssignmentPolicy ↔ Assignment model
```

---

## Admin Auth

Admin has a separate login form at `/admin/login` handled by `Admin\Auth\LoginController`. Regular Breeze auth routes (`/login`, `/register`) are for dosen and mahasiswa. Admin cannot register via the public form — accounts are created by other admins in `/admin/users`.
