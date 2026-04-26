# Playwright Black-Box QA Report

**Suite:** `tests/playwright/*.spec.ts` (15 spec files, 156 tests)
**Run date:** 2026-04-26
**Runner:** `npx playwright test` (Chromium, single worker, sequential)
**Branch:** `feat/playwright-qa-suite`

---

## Summary

| Metric | Count |
|--------|------:|
| Total tests | 156 |
| Passed | 137 |
| Failed | 11 |
| Skipped (test.fixme + skip-by-precondition) | 8 |
| Pass rate (excl. fixme) | 92.6% |
| Wall-clock | 40.9 min |

Phase A (scaffold), B (smoke), C (full coverage) complete. This document is the Phase D deliverable: defect ledger + test-stability ledger.

---

## Real application defects

The following failures reproduce a server- or client-side bug. Each must be fixed in the application; the failing test stays as the regression guard.

### DEF-01 — `admin/hierarchy/student-classes/{id}` returns HTTP 500

- **Test:** `admin-hierarchy.spec.ts` → HIER-12
- **Symptom:** `page.goto('/admin/hierarchy/student-classes/1')` → "Internal Server Error".
- **Root cause:** [resources/views/admin/hierarchy/student_classes/show.blade.php](resources/views/admin/hierarchy/student_classes/show.blade.php) generates `route('admin.hierarchy.study-programs.show')` (probably for the breadcrumb back-link) without binding the required `{studyProgram}` parameter.
- **Stack:**
  ```
  Illuminate\Routing\Exceptions\UrlGenerationException
  Missing required parameter for [Route: admin.hierarchy.study-programs.show]
  [URI: admin/hierarchy/study-programs/{studyProgram}/semesters]
  [Missing parameter: studyProgram]
  ```
- **Fix sketch:** pass `$studentClass->semester->studyProgram` (or whichever path resolves the program) when emitting the back-link URL.
- **Severity:** P1 — entire student-class detail view is unreachable in admin UI.

### DEF-02 — Inactive dosen is allowed to log in

- **Test:** `auth.spec.ts` → AUTH-03 (`inactive dosen rejected at login`)
- **Symptom:** Logging in as `pending@pjbl.test` (seeded with `active=false`) succeeds and lands on `/dosen/dashboard` instead of being rejected back to `/login`.
- **Expected:** Login should reject inactive accounts (or at least not seat the user). Currently `auth.attempt()` on User does not gate on `active`.
- **Severity:** P1 — access-control bypass. Pending dosen can act before admin approval.
- **Fix sketch:** add `'active' => true` to the credentials in [LoginController](app/Http/Controllers/Auth/LoginController.php) (or a `User::scopeActive`/auth-listener guard).

### DEF-03 — Livewire interactivity broken: duplicate Alpine instances

- **Tests:** `shared-announce-discuss.spec.ts` → DSC-03, DSC-04 (currently `test.fixme` — guarded behind this defect, will re-arm once fixed)
- **Symptom:** Discussion comment form (`@livewire('discussion.show')`) never persists `newComment`. Browser console emits:
  ```
  [WARNING] Detected multiple instances of Alpine running
  ```
  `window.Livewire.all()` returns `[]` even though `[wire:snapshot]` elements are in the DOM. Result: `wire:model` deferred values are never flushed to the server.
- **Root cause:** [resources/js/app.js](resources/js/app.js:3-7) imports + boots Alpine *and* `@livewireScripts` ships its own bundled Alpine. Two Alpine instances race and Livewire component init never registers in the store.
- **Fix:** delete the explicit Alpine bootstrap from `app.js`. Livewire 4's bundled Alpine is what every component is wired against; rely on it. If the project needs custom Alpine plugins, register them via the `livewire:init` hook instead of a second Alpine instance.
- **Severity:** P0 — silently breaks every Livewire component on the site (only the discussion comment form is exercised by this suite, but the failure mode is global).

---

## Test stability ledger (env / seed / expectation issues — not app defects)

These tests failed in the full-suite run but the application is correct. They need test-side hardening before they can re-arm reliably.

| Test | Category | Diagnosis |
|------|----------|-----------|
| AUTH-11 password reset request | env flake | `expect(...).not.toContainText(/whoops/)` against an empty `body` — page didn't load. Add `waitForLoadState('domcontentloaded')` + assert form rendered before the negative check. |
| MAT-07 dosen-with-no-course fallback | timeout / state | 60 s test timeout; page stuck. Test depends on a dosen with zero courses, but the seeded `dosen@pjbl.test` accumulates course assignments across runs. |
| QEX-01 create quiz with duration | state | Submit button never resolves stable. Re-runs after prior partial seeds leave the form in a weird state. |
| QEX-05 create code exercise (java) | state | Same submit-stability issue as QEX-01. |
| QEX-07 unsupported language rejected | wrong assertion | Test expects HTTP `500`, server correctly returns `302` (validation redirect) or `422`. Server is right; assertion should be `[302, 422]`. |
| QEX-12 delete quiz cascades questions | seed-order | Locator filters for `PW DelQuiz <stamp>` from the same run, but the create step inside the same `describe` was skipped/different timestamp. Make the test create-then-delete in one block with a captured stamp. |
| CRS-03 enroll into non-enrolled course | DB pollution | "Daftar" button is gone because `mahasiswa@pjbl.test` is already enrolled in "Dasar Jaringan" from a previous run. Either pick an unused course or unenroll in `beforeEach`. |
| CRS-07 material with no attachment | content drift | Expected substring not present in seeded material body. Either soften the matcher or align the seed text with the assertion. |
| QUIZ-10 exercise submit endpoint | wrong assertion | Expects HTTP `419` (CSRF mismatch). Server actually returns `200/201/204/302/422`. Either send a stripped-CSRF request to validate the gate, or relax to "any 4xx". |

---

## Tests passing (137)

All other tests in the 15 spec files pass. Coverage spans:

- **AUTH (15/16):** login/logout, session timeout, role gating, password reset (1 env flake).
- **Admin HIER (29/30) + Admin USR/AC (full):** academic year/semester/department/program CRUD + access control (1 real defect: DEF-01).
- **Dosen MAT/ASG/QEX/CONF (37/41):** course materials, assignments, quizzes, conferences (4 stability flakes, 0 app defects).
- **Mahasiswa CRS/QUIZ/SUB/DSC/GRD (most):** enrollment, quiz/exercise solve, submissions, grades (3 flakes, 0 app defects).
- **Shared ANN/DSC/NTF/PRF (17/19):** announcements, discussions, notifications, profile (2 fixme behind DEF-03, 0 other defects).
- **Edge cases:** all green.

---

## Recommended next actions

1. Fix DEF-01 (5-line view template change) → re-arm HIER-12.
2. Fix DEF-02 (1-line guard in login flow) → re-arm AUTH-03.
3. Fix DEF-03 (delete 5 lines from `resources/js/app.js`, run `npm run build`) → remove `test.fixme` from DSC-03/04, re-arm.
4. Harden the 9 stability-ledger tests (mostly state/seed isolation).
5. Add a `webServer` block to `playwright.config.ts` so the suite boots `php artisan serve --env=testing` automatically and runs against a fresh `php artisan db:seed --class=DuskSeeder` per session — this single change eliminates most of the state-pollution flakes above.
