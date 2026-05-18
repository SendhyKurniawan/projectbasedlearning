# CLAUDE.md

Laravel 12 project-based-learning (PBL) platform. Three roles — `admin`, `dosen` (lecturer), `mahasiswa` (student) — over an academic hierarchy of departments → study programs → student classes → courses. Server-rendered Blade with Tailwind + Alpine; not Inertia, not a Livewire-first app. Domain terms are Indonesian (`mata_kuliah`, `kode_matkul`, `sks`, `nim`, `nip`) — keep them.

## Commands

```bash
composer dev          # concurrent: php artisan serve + queue:listen + pail + vite — preferred dev entry
composer setup        # first-time: install + .env + key + migrate + npm install + build + optimize
composer optimize     # production: config:cache + route:cache + view:cache + event:cache (run after every deploy)
composer test         # config:clear + php artisan test (Pest)
npm run dev           # vite only
npm run build         # production assets
npm run test:pw       # Playwright (also :headed, :ui, :report) — suite is WIP, see Testing
npm run audit:contrast
vendor/bin/pint       # PHP code style
docker compose up -d  # alternative: app + nginx:8000 + MySQL + MailHog
```

## Architecture

- **Roles & routing**: enum `users.role`. Route groups under `/admin`, `/dosen`, `/mahasiswa`, gated by a `role:` middleware. Shared (auth-only): profile, notifications, discussions, announcements, push subscriptions, code-execution proxy.
- **Core entities**: `Course`, `Assignment` (`type` ∈ `tugas|quiz|exercise`), `Submission`, `Group` + `GroupMember`, `QuizQuestion` + `QuizOption`, `Material`, `Conference`, `Discussion`, `Announcement`.
- **Multi-kelas courses**: A dosen teaching the same `kode_matkul` to multiple student classes gets one `Course` row per kelas (same `dosen_id + kode_matkul + semester_id`, different `student_class_id`). These are called *siblings* — `Course::siblings()` returns a Collection of the other rows. The sidebar and dashboard group them visually by `course_group_key` (`dosen_id|nama_matkul|semester_id`). Admin unique validation is composite on `(kode_matkul, dosen_id, semester_id, student_class_id)`, not globally unique on `kode_matkul` alone.
- **Frontend**: `<x-app-layout>` Blade, Alpine sprinkles, CodeMirror + EasyMDE for code/markdown editors. One real Livewire component lives under `app/Livewire/Discussion/` — the rest of the app is plain Blade. Don't reach for Livewire when a Blade form will do. Reusable components: `<x-copy-modal>` (Alpine modal for copying content to sibling kelas).
- **Code execution**: client posts to a server-side proxy that calls Piston API. Throttled 10/min. Don't bypass it from the frontend.
- **Mahasiswa schedule**: `Mahasiswa\ScheduleController::index` (GET `/mahasiswa/jadwal`, `mahasiswa.schedule.index`) shows upcoming conferences + assignments. Uses `$mahasiswa->enrollments()->pluck('courses.id')` via Eloquent relation — exception to the direct-pivot-query convention used elsewhere.

## Conventions

- **Inline validation, no FormRequest classes** outside auth (`LoginRequest`, `ProfileUpdateRequest`). New controllers should match — `$request->validate([...])` directly.
- **Policies, not Gates**: `$this->authorize('view', $course)`. Existing: `AssignmentPolicy`, `CoursePolicy`. Add a policy before adding role checks in a controller.
- **Direct pivot queries**: Mahasiswa flows use `DB::table('enrollments')->where(...)` instead of relations for speed. If you change the `enrollments` schema, grep for raw `DB::table('enrollments'` and update all sites.
- **Boolean validation quirk**: fields like `has_duration` use `'nullable|in:0,1,true,false'` because forms send strings. Mirror this when adding boolean toggles.
- **Create-form fan-out**: Dosen create forms (Material, Assignment, Conference) include `@include('dosen.partials.sibling-kelas-picker')` which renders checkboxes for sibling kelas. The `store()` method handles the primary record first, then loops over valid `sibling_ids` to fan out copies. Same security intersect pattern as copy actions.

## Gotchas

- **Conferencing = Jitsi JaaS only** (LiveKit fully removed). Both `Dosen\ConferenceController::room` and `Mahasiswa\ConferenceController::room` mint an RS256 JWT via `App\Services\JaasTokenService` and pass it to the room view as `$jwt`. Required env vars (now in `.env.example`):
  - `JITSI_DOMAIN` (e.g. `8x8.vc`), `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH` (default `storage/app/private/jaas-private-key.pk`, file must be provisioned manually).
- **WebPush**: `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` are required (now in `.env.example` as empty placeholders — fill before push works).
- **No auto-grading for exercises**: `Mahasiswa\ExerciseController::submit` runs `validateCode()` (keyword match) only as a *hint* stored in `submissions.validation_result` — score is left null, `auto_graded=false`, `status='submitted'`. Dosen grade manually via `Dosen\AssignmentController::grade`. Dosen submissions view (`resources/views/dosen/assignments/submissions.blade.php`) renders the validation hint as "Validasi Mesin". The `auto_grade` column on `assignments` does NOT exist; an earlier WIP design was reverted.
- **Quiz partial auto-grading**: MC questions scored automatically. `QuizController::submit` sets `status='graded'` only when no `essay`/`code_snippet` questions exist; otherwise `status='submitted'` with provisional MC score awaiting dosen review.
- **Queue driver = `database`, sync by default**. Only `ResetPasswordNotification` is `Queueable`. Other notifications dispatch synchronously — adding `ShouldQueue` is a behavior change.
- **`BROADCAST_CONNECTION=log`** — there's no Pusher/Reverb wired up. No `config/broadcasting.php`. Don't reach for `broadcast()` without setting up a driver first.
- **Copy-to-kelas fan-out security**: `Material::copy`, `Assignment::copy`, `Conference::copy` all intersect the submitted `sibling_ids` with `$course->siblings()->pluck('id')` before acting. Never skip this — it prevents a dosen from targeting another dosen's courses by crafting a POST. Material files are physically copied per sibling (unique `_kelas{id}_{time()}` suffix) to avoid shared-path deletion side effects. Quiz copies create a shell with no questions — message tells dosen to add questions separately.
- **`exercise_config` is a JSON cast column** on `assignments` — stores `language`, `starter_code`, `solution_code`, `required_keywords`, `hints`. There are no standalone columns for these. Use `$assignment->exercise_config['key']` or pass the whole cast array. Don't add them to `$assignment->only([...])` calls expecting real columns.

## Testing

- **Pest** under `tests/Feature` and `tests/Unit`. `composer test` is the canonical entry.
- **Laravel Dusk** under `tests/Browser` with `.env.dusk.local` (SQLite in-memory). `DuskSeeder` is idempotent — only seeds when `User::count() === 0`. Force a clean state with `php artisan migrate:fresh --seeder=DuskSeeder`.
- **Playwright** suite is in progress on `feat/playwright-qa-suite` — no test files or `playwright.config.*` committed yet. `package.json` has the `test:pw*` scripts ready; add `playwright.config.ts` at the repo root to activate.

## Working in this repo

- Operate from `D:\Projects\pjbl\`. Ignore `.claude/worktrees/*`.
- Don't use destructive git recovery (`git reset --hard`, `git checkout .`) to fix a broken state — describe and ask first.
