# CLAUDE.md

Laravel 12 project-based-learning (PBL) platform. Three roles — `admin`, `dosen` (lecturer), `mahasiswa` (student) — over an academic hierarchy of departments → study programs → student classes → courses. Server-rendered Blade with Tailwind + Alpine; not Inertia, not a Livewire-first app. Domain terms are Indonesian (`mata_kuliah`, `kode_matkul`, `sks`, `nim`, `nip`) — keep them.

## Commands

```bash
composer dev          # concurrent: php artisan serve + queue:listen + pail + vite — preferred dev entry
composer setup        # first-time: install + .env + key + migrate + npm install + build
composer test         # config:clear + php artisan test (Pest)
npm run dev           # vite only
npm run build         # production assets
npm run test:pw       # Playwright (also :headed, :ui, :report) — suite is WIP, see Testing
npm run audit:contrast
vendor/bin/pint       # PHP code style
```

## Architecture

- **Roles & routing**: enum `users.role`. Route groups under `/admin`, `/dosen`, `/mahasiswa`, gated by a `role:` middleware. Shared (auth-only): profile, notifications, discussions, announcements, push subscriptions, code-execution proxy.
- **Core entities**: `Course`, `Assignment` (`type` ∈ `tugas|quiz|exercise`), `Submission`, `Group` + `GroupMember`, `QuizQuestion` + `QuizOption`, `Material`, `Conference`, `Discussion`, `Announcement`.
- **Frontend**: `<x-app-layout>` Blade, Alpine sprinkles, CodeMirror + EasyMDE for code/markdown editors. One real Livewire component lives under `app/Livewire/Discussion/` — the rest of the app is plain Blade. Don't reach for Livewire when a Blade form will do.
- **Code execution**: client posts to a server-side proxy that calls Piston API. Throttled 10/min. Don't bypass it from the frontend.

## Conventions

- **Inline validation, no FormRequest classes** outside auth (`LoginRequest`, `ProfileUpdateRequest`). New controllers should match — `$request->validate([...])` directly.
- **Policies, not Gates**: `$this->authorize('view', $course)`. Existing: `AssignmentPolicy`, `CoursePolicy`. Add a policy before adding role checks in a controller.
- **Direct pivot queries**: Mahasiswa flows use `DB::table('enrollments')->where(...)` instead of relations for speed. If you change the `enrollments` schema, grep for raw `DB::table('enrollments'` and update all sites.
- **Boolean validation quirk**: fields like `has_duration` use `'nullable|in:0,1,true,false'` because forms send strings. Mirror this when adding boolean toggles.

## Gotchas

- **Conferencing was migrated LiveKit → Jitsi/JaaS** (commit `b2ceb00`). Required env vars are NOT in `.env.example`:
  - `JITSI_DOMAIN` (e.g. `8x8.vc`), `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH` (default expects `storage/app/private/jaas-private-key.pk`, must be provisioned manually).
  - `composer.json` still requires `agence104/livekit-server-sdk` even though it's no longer used at runtime — leave it for now unless cleaning up deliberately.
- **WebPush**: `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` are required and also missing from `.env.example`.
- **No auto-grading for exercises**: `Mahasiswa\ExerciseController::submit` runs `validateCode()` (keyword match) only as a *hint* stored in `submissions.validation_result` — score is left null, `auto_graded=false`, `status='submitted'`. Dosen grade manually via `Dosen\AssignmentController::grade`. Dosen submissions view (`resources/views/dosen/assignments/submissions.blade.php`) renders the validation hint as "Validasi Mesin". The `auto_grade` column on `assignments` does NOT exist; an earlier WIP design was reverted.
- **Quiz partial auto-grading**: MC questions scored automatically. `QuizController::submit` sets `status='graded'` only when no `essay`/`code_snippet` questions exist; otherwise `status='submitted'` with provisional MC score awaiting dosen review.
- **Queue driver = `database`, sync by default**. Only `ResetPasswordNotification` is `Queueable`. Other notifications dispatch synchronously — adding `ShouldQueue` is a behavior change.
- **`BROADCAST_CONNECTION=log`** — there's no Pusher/Reverb wired up. No `config/broadcasting.php`. Don't reach for `broadcast()` without setting up a driver first.

## Testing

- **Pest** under `tests/Feature` and `tests/Unit`. `composer test` is the canonical entry.
- **Laravel Dusk** under `tests/Browser` with `.env.dusk.local` (SQLite in-memory). `DuskSeeder` is idempotent — only seeds when `User::count() === 0`. Force a clean state with `php artisan migrate:fresh --seeder=DuskSeeder`.
- **Playwright** is currently scaffolding only — `tests/playwright/{assets,helpers}` exist but no specs are committed. The `feat/playwright-qa-suite` branch is where this is being built. `package.json` has the `test:pw*` scripts; `playwright.config.*` lives at the repo root when the suite is active.

## Working in this repo

- Operate from `D:\Projects\pjbl\`. Ignore `.claude/worktrees/*`.
- Don't use destructive git recovery (`git reset --hard`, `git checkout .`) to fix a broken state — describe and ask first.
