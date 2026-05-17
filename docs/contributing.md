# Contributing

## Branch Model

Branch off `main`. Name branches `feat/`, `fix/`, or `chore/` followed by a short description. PRs target `main` directly.

---

## Before Committing

```bash
vendor/bin/pint       # fix PHP code style (PSR-12 + Laravel presets)
composer test         # must be green before merging
```

Pint is non-negotiable — CI will fail on style violations. Run it after every significant change, not just at the end.

---

## Code Conventions

### Validation — inline only

```php
// Correct — inline in controller
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'deadline' => 'required|date|after:today',
]);

// Wrong — no FormRequest classes except LoginRequest and ProfileUpdateRequest
class StoreAssignmentRequest extends FormRequest { ... }
```

### Authorization — Policies, not Gates

```php
// Correct
$this->authorize('update', $course);

// Wrong — don't add role checks inline or use Gate::check
if ($user->role !== 'dosen') abort(403);
Gate::check('update-course', $course);
```

If a new resource needs authorization, add a Policy class and register it in `AuthServiceProvider`. Don't build ad-hoc role checks in controllers.

### Indonesian domain terms

Keep `kode_matkul`, `sks`, `nim`, `nip`, `mata_kuliah`, `dosen`, `mahasiswa` as-is in code, variable names, and docs. These are domain terms, not typos.

### Boolean form fields

HTML forms send `"1"` and `"0"` as strings. Validate boolean toggles like:

```php
'has_duration' => 'nullable|in:0,1,true,false',
```

Then cast in the model (`'is_group' => 'boolean'` in `casts()`). Don't use `'boolean'` as the validation rule alone — it rejects string `"1"`.

---

## Copy Fan-out Security

When a dosen copies content (Material, Assignment, Conference) to sibling kelas, always intersect the submitted IDs with the course's actual siblings:

```php
$validSiblingIds = $course->siblings()->pluck('id')->toArray();
$targetIds = array_intersect($request->sibling_ids ?? [], $validSiblingIds);

foreach ($targetIds as $siblingId) {
    // copy to $siblingId
}
```

**Never skip this intersect.** A crafted POST with arbitrary `sibling_ids` could target another dosen's courses. This is the security boundary for all fan-out operations.

Material files are physically copied per sibling (unique `_kelas{id}_{time()}` suffix) to prevent shared-path deletion side effects when one copy is removed.

---

## Adding a New Assignment Type

If you add a fourth type alongside `tugas | quiz | exercise`:

1. **`Assignment` model** — document the new type in the `type` column PHPDoc
2. **`Dosen\AssignmentController`** — add a create/store/edit/update branch for the new type's fields
3. **View partials** — add partials in `resources/views/dosen/assignments/` for the new type's form fields
4. **Mahasiswa submission flow** — add a route and controller for student submission
5. **Seeder** — add at least one example in `AssignmentSeeder` so devs have test data

---

## Adding Enrollment Logic

Mahasiswa enrollment uses `DB::table('enrollments')` raw queries throughout `Mahasiswa\*` controllers. If you change the `enrollments` schema (add columns, rename `mahasiswa_id`):

```bash
grep -r "DB::table('enrollments'" app/
```

Update every site. The Eloquent relation `Course::students()` also exists but may not be used in all places.

---

## Livewire vs. Blade

There is one real Livewire component: `app/Livewire/Discussion/Show.php`. It exists specifically for reactive comment submission without a full page reload.

**Don't reach for Livewire for new features.** If you need interactivity, use Alpine.js. If you need a server roundtrip, use a regular Blade form with a redirect. Only add a Livewire component if you genuinely need reactive server state that Alpine can't provide locally.

---

## Quiz Copy Behavior

When a quiz is copied to sibling kelas, the quiz questions are **not** copied — only a shell assignment is created. The controller notifies dosen to add questions to the copy separately. This is intentional: question sets often need kelas-specific adjustments.
