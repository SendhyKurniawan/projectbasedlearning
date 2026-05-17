# Testing

## Pest (Unit + Feature)

```bash
composer test   # config:clear → php artisan test
```

Test files live in `tests/Feature/` and `tests/Unit/`. Pest is the test runner; the Laravel plugin gives access to `actingAs()`, `assertDatabaseHas()`, RefreshDatabase, etc.

### Patterns from the existing suite

**Role auth guards** — most feature tests assert that the wrong role or a guest gets a 403/redirect:

```php
it('requires dosen auth', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    actingAs($mahasiswa)->get(route('dosen.dashboard'))->assertForbidden();
});
```

**Ownership checks** — dosen cannot touch another dosen's course:

```php
it('prevents cross-dosen course access', function () {
    $other = User::factory()->dosen()->create();
    $course = Course::factory()->for($other, 'dosen')->create();
    actingAs($this->dosen)->get(route('dosen.materials.index', $course))->assertForbidden();
});
```

---

## Laravel Dusk

`tests/Browser/` holds browser tests run by Dusk against a real browser.

**Env**: `.env.dusk.local` — uses SQLite in-memory for speed. DB_CONNECTION should point to a test database, not prod.

**Seeder**: `DuskSeeder` is idempotent — checks `User::count() === 0` before seeding. Force a clean state:

```bash
php artisan migrate:fresh --seeder=DuskSeeder
```

**Page objects**: `tests/Browser/Pages/` directory exists but is currently empty — page object scaffolding for future tests.

**Run Dusk**:

```bash
php artisan dusk              # headless
php artisan dusk --browse     # headed
```

Dusk requires Chrome/Chromedriver. `laravel/dusk` is not in `composer.json` at the time of writing — check if it needs installing: `composer require --dev laravel/dusk` then `php artisan dusk:install`.

---

## Playwright (WIP)

Playwright is scaffolded on the `feat/playwright-qa-suite` branch. No specs are committed yet.

```bash
npm run test:pw         # headless
npm run test:pw:headed  # headed browser
npm run test:pw:ui      # Playwright UI mode (interactive)
npm run test:pw:report  # open last HTML report
```

Config: `playwright.config.js` (and optionally `playwright.config.ts`) at repo root.

Structure:

```
tests/playwright/
├── assets/         # fixtures, test data files
└── helpers/        # shared utilities (login helpers, etc.)
```

No spec files exist yet. When writing specs, use the helpers in `tests/playwright/helpers/` and seed accounts from the table below.

---

## Seeded Test Accounts

All passwords: `password`

| Role | Email |
|---|---|
| Admin | `admin@pjbl.test` |
| Dosen | `dosen@pjbl.test` |
| Mahasiswa | `mahasiswa@pjbl.test` |

Use `mahasiswa@pjbl.test` for student flows — it's assigned to `student_class_id = 1` which is enrolled in the seeded courses.
