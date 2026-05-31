# Contributing

## Branch model

Branch off `main`. Use the `feat/`, `fix/`, `chore/`, `refactor/`, `docs/` prefixes followed by a short kebab description (e.g. `feat/quiz-timer-warning`, `fix/conference-end-redirect`). PRs target `main` directly. Squash-merge unless there's a reason not to.

---

## Before committing

```bash
vendor/bin/pint       # PSR-12 + Laravel preset
composer test         # Pest suite (config:clear → php artisan test)
```

Pint is non-negotiable — run it on every save, not just at PR time. CI will fail on a violation.

For UI changes, also:

```bash
npm run audit:contrast   # if you touched design-system.css colours
npm run build            # confirm Vite builds cleanly
```

Manual smoke-test the changed screens in a real browser before opening the PR. Type checks and tests verify code correctness, not feature correctness — if you can't manually test the UI, say so in the PR description.

---

## Code conventions

### Validation — inline only

```php
// Correct — inline in controller
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'deadline' => 'required|date|after:now',
]);

// Wrong — no FormRequest classes outside Breeze auth
// The only existing ones are LoginRequest and ProfileUpdateRequest.
class StoreAssignmentRequest extends FormRequest { ... }
```

If the same validation block is duplicated across `store()` and `update()`, copy it. Don't extract a FormRequest just for DRY.

### Authorization — Policies, never Gates

```php
// Correct
$this->authorize('update', $course);
$this->authorize('view', $assignment);

// Wrong
if ($user->role !== 'dosen') abort(403);          // ad-hoc role check
Gate::check('update-course', $course);            // Gate facade
```

Existing policies: `CoursePolicy`, `AssignmentPolicy` (auto-discovered by Laravel's naming convention). If a new resource needs auth, add a policy class first.

For mahasiswa-side flows that need enrollment checks, do an explicit `DB::table('enrollments')->where(...)->exists()` inline — that's the current convention, not a policy. See [database.md](database.md) for the convention exception list.

### Indonesian domain terms

Keep these as-is in code, identifiers, and docs:

`mata_kuliah`, `kode_matkul`, `nama_matkul`, `sks`, `nim`, `nip`, `dosen`, `mahasiswa`, `akademik`, `kelas`, `tugas`, `pilihan_ganda`, `essay`, `code_snippet`.

They are domain terms, not typos. Do not rename them to English.

### Boolean form fields

HTML forms POST `"1"` and `"0"` as strings. The `boolean` validation rule accepts those plus actual booleans (and `null` when the field isn't submitted). Use it:

```php
$request->validate([
    'is_group' => 'nullable|boolean',
]);

// And cast in the model:
protected function casts(): array
{
    return ['is_group' => 'boolean'];
}
```

Some older code uses `'nullable|in:0,1,true,false'` instead — both work; pick `boolean` for new code.

### Don't add notifications without checking the queue setup

Every notification class in `app/Notifications/` implements `ShouldQueue`. Under the default `.env.example` (`QUEUE_CONNECTION=sync`) they dispatch inline. In production (`QUEUE_CONNECTION=database`) they require a running queue worker. When adding a notification class, default to `ShouldQueue` to match the rest of the suite, and warn in the PR description if the new notification will fan out to many recipients (which makes the synchronous code path slow).

---

## Copy fan-out security (do not skip)

When a dosen copies content (Material, Assignment, Conference, Exercise) to sibling kelas — either at create time or via a copy endpoint — always intersect the submitted IDs with the course's actual siblings before acting:

```php
$allowedSiblingIds = $course->siblings()->pluck('id');
$targetIds = collect($request->sibling_ids ?? [])
    ->map(fn($id) => (int) $id)
    ->intersect($allowedSiblingIds);

if ($targetIds->isEmpty()) {
    return back()->with('error', 'Pilih kelas tujuan yang valid.');
}

$targetCourses = Course::whereIn('id', $targetIds)->get();
foreach ($targetCourses as $sibling) {
    // act on $sibling — never on raw $request->sibling_ids
}
```

**Never skip this intersect.** Without it, a crafted POST with arbitrary `sibling_ids` would target other dosens' courses.

Material file fan-out also physically copies the file with a unique suffix (`_kelas{id}` on create-fanout, `_kelas{id}_{time()}` on later copy) to avoid sharing a path. See `Dosen\MaterialController::store()` and `::copy()` for the exact pattern.

Quiz copies create a **shell** assignment with no questions. The success message tells the dosen to add questions to each copy separately. Don't change this — question sets often need kelas-specific tweaks.

---

## Adding a new assignment type

If you add a fourth `assignment.type` value alongside `tugas | quiz | exercise`:

1. **Migration** — extend the enum: `Schema::table('assignments', fn ($t) => $t->enum('type', ['tugas','quiz','exercise','new'])->change())`. Use Laravel's `change()` (which requires `doctrine/dbal` in older Laravels; 12.x ships with native support).
2. **`Assignment` model** — update the type PHPDoc and any helpers.
3. **`Dosen\AssignmentController`** — extend the `store()` validation and the per-type branching. Decide whether the new type needs `submission_format`, `duration_minutes`, `is_group`, etc.
4. **`Dosen\AssignmentController::copy()`** — make sure `$assignment->only([…])` includes any new columns.
5. **View partials** — add partials under `resources/views/dosen/assignments/` for the type's form fields.
6. **Mahasiswa submission flow** — add a route + controller. Decide whether `CheckAssignmentUnlocked` should gate it (it should — apply the `check.assignment.unlocked` middleware).
7. **Sibling fan-out** — confirm `siblings()` and the security intersect still apply.
8. **Seeder** — add at least one example in `AssignmentSeeder` so devs have test data.

---

## Adding enrollment logic

Mahasiswa enrollment uses `DB::table('enrollments')->where(...)` raw queries throughout `Mahasiswa\*` controllers (exceptions: `ScheduleController`, `SubmissionController`, `DashboardController` use the `User::enrollments()` relation). If you change the `enrollments` schema:

```bash
grep -r "DB::table('enrollments'" app/
grep -r "->enrollments()"          app/
grep -r "->enrolledCourses()"      app/   # alias relation defined on User
```

Update every site.

---

## Livewire vs. Blade

There is one real Livewire component: `App\Livewire\Discussion\Show`. It exists so that comment posting can re-render the comment list without a full page reload — Alpine alone can't do that.

**Don't reach for Livewire for new features.** If you need:

- pure client-side interactivity → Alpine
- server-side state with a redirect → plain Blade form + redirect
- partial reactive re-render → only then consider Livewire

Livewire 4 has a [known quirk where `redirect()` to the same URL won't refresh Blade content outside the component](https://github.com/livewire/livewire/issues/…) — if you hit this, use `loadX()` to refresh in-component or `redirect(..., navigate: false)`.

Playwright + Livewire `wire:model` quirk: `.fill()` short-circuits the input event sequence and submits empty state. Use slow `.pressSequentially(text, { delay: 30 })` instead.

---

## Working in this repo

- Operate from `D:\Projects\pjbl\`. Ignore `.claude/worktrees/*`.
- Don't use destructive git recovery (`git reset --hard`, `git checkout .`, `git clean -f`) to fix a broken state. Describe the situation and ask first.
- Don't strip the Indonesian terms.
- Commit messages use Conventional Commits-ish prefixes: `feat(area): …`, `fix(area): …`, `refactor(area): …`, `chore(area): …`, `docs(area): …`. See `git log` for examples.
- Don't add `Co-Authored-By: Claude` or "🤖 Generated with Claude Code" lines to commits/PRs — strip them if they appear.

---

## PR description template

```markdown
## Summary
- one-line description of what changed
- second bullet for the why
- third bullet for any noteworthy decision

## Test plan
- [ ] Pest passes (`composer test`)
- [ ] Pint clean (`vendor/bin/pint --test`)
- [ ] Manually walked through <feature> as <role>
- [ ] (if env changes) `.env.example` updated
- [ ] (if schema changes) `docs/database.md` updated
- [ ] (if Vite entry added) note about removing `pjbl_app_build` volume on deploy
```
