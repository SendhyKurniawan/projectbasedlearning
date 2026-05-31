# Profile

Self-service profile editing for the logged-in user. Shared across all roles. Routed under the auth-only middleware group.

```php
// routes/web.php (auth group)
Route::get   ('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch ('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
```

This is one of only two places in the app that use a `FormRequest` class (the other is `LoginRequest`). See [contributing.md](../contributing.md#validation--inline-only) — inline `$request->validate()` is the rule everywhere else.

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

Mahasiswa get extra eager-loading of their full academic context (department → study program → kelas → semester → academic year) so the profile page can render their cohort without N+1 queries.

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

Email changes null out `email_verified_at`. The user will need to re-verify via the standard Breeze verification flow (`verification.notice` / `verification.verify`) — but the `is_active` gate in `AuthenticatedSessionController::store` does **not** look at `email_verified_at`, so editing the email does not log the user out or block subsequent logins.

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

Account deletion requires re-entering the current password (`current_password` rule re-checks the bcrypt hash). On success: log out, delete the row, regenerate session token, redirect to `/`.

Cascade deletes triggered by `User` deletion:
- `enrollments` (cascade)
- `submissions` (cascade)
- `material_views` (cascade)
- `courses` where `dosen_id` (cascade — **all their courses disappear**; consider archiving instead before allowing dosen deletion)
- `notifications` (polymorphic morph — not cascaded; orphan rows in `notifications.notifiable_id`)
- `push_subscriptions` (polymorphic morph — same orphan concern)
- `discussions`, `discussion_comments` (cascade)

The orphans (`notifications`, `push_subscriptions`) aren't cleaned automatically because the morph doesn't have a FK. They'll never be queried for a missing user, but they accumulate. Not a correctness issue, but a tidiness concern — periodically prune with `DELETE FROM notifications WHERE notifiable_id NOT IN (SELECT id FROM users)`.

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

Editable fields:

| Field | Notes |
|---|---|
| `name` | required |
| `email` | required, lowercase, must be unique among users (excluding self) |
| `nim` | optional, unique among users (excluding self) — mahasiswa only in practice |
| `nip` | optional, unique among users (excluding self) — dosen only in practice |
| `sso_id` | reserved for future SSO integration; no validation other than max:255 |

Fields explicitly **not** editable through this form:

- `role` — only admin can change (`/admin/users/{user}/edit`)
- `is_active` — only admin (`/admin/users/{user}/toggle-active`)
- `student_class_id` — only admin (`/admin/akademik/classes/{kelas}/students`)
- `password` — goes through `PUT /password` (`PasswordController::update`, Breeze flow)

`mahasiswa` accidentally submitting a `nip` (or vice-versa) will pass validation (`nullable`) and overwrite the column. The validation doesn't enforce role-appropriate IDs; that's a frontend convention. If you want to enforce it server-side, add:

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

The page renders three sections (Breeze defaults adapted for this project):

1. **Profile information** — POSTs to `profile.update`. Displays the success flash via `session('status') === 'profile-updated'`.
2. **Update password** — POSTs to `password.update` (the separate `PasswordController`). Requires current + new + confirmation.
3. **Delete account** — POSTs to `profile.destroy` with a confirm modal that asks for the password.

For mahasiswa, the page also shows the loaded academic context (kelas, study program, department, semester, academic year) read-only — useful as a "show me what the system thinks I am" panel.

---

## Password change

`PUT /password` (`Auth\PasswordController::update`, Breeze default):

```php
$validated = $request->validateWithBag('updatePassword', [
    'current_password' => ['required', 'current_password'],
    'password' => ['required', Password::defaults(), 'confirmed'],
]);

$request->user()->update([
    'password' => Hash::make($validated['password']),
]);
```

`Password::defaults()` in Breeze applies the project's password rules — by default that's `min(8)` + `mixedCase` + `numbers` + `symbols` + `uncompromised` (HaveIBeenPwned check). Verify in `app/Providers/AppServiceProvider::boot()` if you need to relax it.

---

## Authorization

There is no policy on `ProfileController`. Every action mutates the row of the **currently logged-in user** (`$request->user()`) — there's no `{user}` route binding, so cross-user mutation isn't possible. Admin uses the separate `/admin/users/*` resource (`Admin\UserController`) for editing other people's accounts.

| Route | Guard |
|---|---|
| `GET /profile` | `auth` |
| `PATCH /profile` | `auth` |
| `DELETE /profile` | `auth` + current password |
| `PUT /password` | `auth` + current password |

The OTP flow (registration verification) and password reset flow are separate — see [auth-roles.md](../auth-roles.md#registration--otp-verification).

---

## Related: shared-route reference

| URL | Method | Controller | Purpose |
|---|---|---|---|
| `/profile` | GET | `ProfileController::edit` | view + edit form |
| `/profile` | PATCH | `ProfileController::update` | mutate name/email/nim/nip/sso_id |
| `/profile` | DELETE | `ProfileController::destroy` | self-delete account |
| `/password` | PUT | `Auth\PasswordController::update` | change password |
| `/confirm-password` | GET/POST | `Auth\ConfirmablePasswordController` | re-confirm for sensitive ops |
| `/email/verification-notification` | POST | `EmailVerificationNotificationController::store` | resend verification email (throttle 6/min) |
| `/verify-email/{id}/{hash}` | GET | `VerifyEmailController` | signed link callback |
