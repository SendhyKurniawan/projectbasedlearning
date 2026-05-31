# Testing

## Test layouts

| Path | Runner | Status |
|---|---|---|
| `tests/Feature/` | Pest + `RefreshDatabase` | active, primary suite |
| `tests/Unit/` | Pest | active (small, mostly factory/model) |
| `tests/Browser/` | Pest with Dusk binding (`DuskTestCase`) | scaffolded — directories exist (`Admin/`, `Dosen/`, `Mahasiswa/`, `Auth/`, `Components/`, `Concerns/`, `Pages/`, `Shared/`, `console/`, `fixtures/`, `screenshots/`, `source/`) but no spec files committed yet |
| `tests/playwright/` | Playwright | WIP — `assets/`, `helpers/` skeleton present; **no `playwright.config.ts` yet**, no specs. The `package.json` scripts (`test:pw`, `test:pw:headed`, `test:pw:ui`, `test:pw:report`) are wired up. |

---

## Pest (Feature + Unit)

```bash
composer test         # config:clear → php artisan test
```

`composer test` is the canonical entrypoint. It calls `php artisan config:clear --ansi` first so that an old config cache doesn't override `.env.testing`.

### Bindings (`tests/Pest.php`)

```php
pest()->extend(Tests\DuskTestCase::class)
      ->beforeEach(function () {
          // Seed only when DB is empty to avoid concurrent-seeder conflicts.
          // Run `php artisan migrate:fresh --seeder=DuskSeeder` to force a fresh seed.
          if (\App\Models\User::count() === 0) {
              $this->seed(\Database\Seeders\DuskSeeder::class);
          }
      })
      ->in('Browser');

pest()->extend(Tests\TestCase::class)
      ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
      ->in('Feature');
```

`Feature` tests reset the DB per test with `RefreshDatabase`. `Browser` tests share state and seed on first run only.

> **Heads-up**: `DuskSeeder` is referenced but **not** present in `database/seeders/` at the moment. If you start writing browser tests, create one before running `composer test` against `tests/Browser/`. The existing seeders (`UserSeeder`, `CourseSeeder`, etc.) are still usable for ad-hoc seeding.

### Files currently committed

```
tests/Feature/
├── AssignmentModelTest.php
├── ConferenceTest.php
├── CourseModelTest.php
├── DosenAssignmentTest.php
├── DosenMaterialTest.php
├── ExampleTest.php
├── MahasiswaSubmissionTest.php
├── MiddlewareTest.php
├── ProfileTest.php
├── ProfileUpdateTest.php
├── SubmissionModelTest.php
└── Auth/
    ├── AuthenticationTest.php
    ├── EmailVerificationTest.php
    ├── PasswordConfirmationTest.php
    ├── PasswordResetTest.php
    ├── PasswordUpdateTest.php
    └── RegistrationTest.php

tests/Unit/
├── ExampleTest.php
└── UserModelTest.php
```

### Common patterns

**Role auth guards** — most feature tests assert that the wrong role or a guest gets a 403/redirect:

```php
test('mahasiswa cannot reach dosen dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    $this->actingAs($mahasiswa)->get(route('dosen.dashboard'))->assertRedirect(route('mahasiswa.dashboard'));
});
```

(Note: the `role` middleware redirects to the user's own dashboard rather than 403 — see [auth-roles.md](auth-roles.md).)

**Ownership checks** — a dosen cannot touch another dosen's course:

```php
test('dosen cannot view another dosens course', function () {
    $other = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->for($other, 'dosen')->create();
    $this->actingAs($this->dosen)->get(route('dosen.materials.index', $course))->assertForbidden();
});
```

**Login redirect** (`Auth/AuthenticationTest.php` already in the suite):

```php
test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticated();
    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
});
```

### Factory hooks

| Factory | Notes |
|---|---|
| `UserFactory` | Default role is `mahasiswa`; override with `['role' => 'dosen' \| 'admin']`. `is_active` defaults to true. |
| `CourseFactory` | Provides `dosen`, `semester`, `student_class` if relations are not set. |
| `AssignmentFactory` | Defaults to `type=tugas`. Override `type=quiz` or `type=exercise` (and provide `exercise_config`). |
| `MaterialFactory`, `SubmissionFactory`, `ConferenceFactory` | Standard relationship-aware factories. |
| Academic factories: `AcademicYearFactory`, `SemesterFactory`, `DepartmentFactory`, `StudyProgramFactory`, `StudentClassFactory`. | |

---

## Laravel Dusk

The suite uses **Pest with Dusk binding**. Dusk drives a real Chrome through ChromeDriver.

```bash
php artisan dusk              # headless
php artisan dusk --browse     # headed
```

`laravel/dusk` is **not** in `composer.json` right now. To start using Dusk: `composer require --dev laravel/dusk` then `php artisan dusk:install` (creates `tests/DuskTestCase.php` plus `.env.dusk.local` template).

Suggested `.env.dusk.local`:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
JITSI_DOMAIN=meet.local.test
JITSI_JWT_APP_ID=test
JITSI_JWT_APP_SECRET=00000000000000000000000000000000
```

Force a clean state any time:

```bash
php artisan migrate:fresh --seeder=DuskSeeder
```

The page-object directory `tests/Browser/Pages/` exists but is empty. The `tests/Browser/console/`, `screenshots/`, and `source/` dirs are tracked but `.gitignore`'d for outputs.

---

## Playwright (WIP)

`tests/playwright/` is the planned home for Playwright specs but no `playwright.config.ts` is committed yet. Until you add the config + specs, the npm scripts will exit with `No tests found.`

```bash
npm run test:pw         # headless
npm run test:pw:headed  # headed browser
npm run test:pw:ui      # Playwright UI mode (interactive)
npm run test:pw:report  # open the last HTML report
```

When wiring this up:

1. `npm i -D @playwright/test` is already in `devDependencies` (`^1.59.1`).
2. Add `playwright.config.ts` at the repo root.
3. Set `testDir: './tests/playwright'` and a `webServer` block that runs `php artisan serve` against `.env.testing`.
4. Use the seeded accounts (table below) — do not register new users in tests.
5. For Livewire-backed forms (the discussion comment), use slow typing — `page.locator('textarea').pressSequentially(text, { delay: 30 })` — not `.fill()`. `wire:model` does not commit when `fill()` short-circuits the input event sequence; the form will submit empty.

---

## Seeded test accounts

`database/seeders/UserSeeder.php` creates:

| Role | Email | Notes |
|---|---|---|
| Admin | `admin@pjbl.test` | password `password` |
| Dosen (primary) | `dosen@pjbl.test` | |
| Dosen | `budi.dosen@pjbl.test` | |
| Dosen | `siti.dosen@pjbl.test` | |
| Mahasiswa (primary) | `mahasiswa@pjbl.test` | `student_class_id = 1` |
| Mahasiswa | `ahmad.mhs@pjbl.test`, `dewi.mhs@…`, `cahya.mhs@…`, `rina.mhs@…`, `fajar.mhs@…` | random `student_class_id ∈ {1,2}` |

All passwords are `password`. Use `mahasiswa@pjbl.test` for student flows; it's the one in `student_class_id=1` which gets enrolled in the seeded courses.

`DatabaseSeeder::run()` calls (in order): `AcademicYearSeeder`, `SemesterSeeder`, `DepartmentSeeder`, `StudyProgramSeeder`, `StudentClassSeeder`, `UserSeeder`, `CourseSeeder`, `DummyDataSeeder`, `AssignmentSeeder`. Run with:

```bash
php artisan migrate:fresh --seed
```

---

## Static analysis & style

```bash
vendor/bin/pint         # PHP code style — PSR-12 + Laravel preset
```

There is no PHPStan or Psalm configuration in this repo at present. CI relies on Pint + Pest only.

---

## Code execution sandbox in tests

`POST /execute-code` is throttled `10/min` per IP. Tests that exercise the proxy should either:

- mock `Illuminate\Support\Facades\Http::fake([…])` in feature tests; or
- swap `services.piston.url` to a local fake before running.

Do not actually call the public `emkc.org` endpoint from CI.
