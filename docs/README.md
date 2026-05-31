# PBL Workspace — Documentation Index

Server-rendered Laravel 12 LMS for Project-Based Learning. Three roles (`admin`, `dosen`, `mahasiswa`) over an academic hierarchy (department → study program → kelas → courses). Blade + Tailwind + Alpine; one Livewire component (`Discussion\Show`). Self-hosted Jitsi for conferences (HS256 JWT, new-tab launcher). Notifications go to `database` + `webpush` channels.

Start with **getting-started.md** if you're setting up locally. Start with **architecture.md** if you're new to the codebase.

---

## Top-level

| Doc | What it covers |
|---|---|
| [getting-started.md](getting-started.md) | Prerequisites, native + Docker setup, env var reference, seeded accounts, daily commands, first-time troubleshooting |
| [architecture.md](architecture.md) | The three-role system, academic hierarchy, siblings + `course_group_key`, copy fan-out, request flow, Piston code execution, conventions |
| [auth-roles.md](auth-roles.md) | `role` enum, route middleware, `CheckRole` + `CheckAssignmentUnlocked`, policies, admin login, registration + OTP + admin approval flow |
| [database.md](database.md) | Schema reference per table, composite keys, conventions (`DB::table('enrollments')` raw queries), gotchas |
| [frontend.md](frontend.md) | Vite entry points, layouts, Alpine patterns, Blade components, editors (CodeMirror/EasyMDE), Chart.js, design system, push subscription bootstrap |
| [testing.md](testing.md) | Pest (Feature + Unit), Dusk scaffolding, Playwright WIP, seeded accounts |
| [deployment.md](deployment.md) | Production topology (Caddy + Docker), services, production checklist, env vars, Jitsi/VAPID provisioning, queue worker, mail, file storage, rollback, logging |
| [contributing.md](contributing.md) | Branch model, code conventions, copy-fan-out security, adding assignment types, Livewire constraints, PR template |

## Feature docs

| Doc | What it covers |
|---|---|
| [features/courses.md](features/courses.md) | Course model, siblings, `course_group_key`, enrollment, sidebar cache, mahasiswa learning path, copy fan-out, bare-URL fallbacks |
| [features/materials.md](features/materials.md) | Material model, dosen authoring routes, EasyMDE/CodeMirror, reorder, file handling and fan-out, mahasiswa view + `MaterialView` recording |
| [features/assignments.md](features/assignments.md) | Three types (`tugas / quiz / exercise`), dosen authoring routes, quiz question types, exercise `exercise_config`, material prerequisite, copy fan-out, notifications |
| [features/submissions.md](features/submissions.md) | Lifecycle, tugas submission (individual + group), quiz submit semantics, exercise submit + `validation_result`, group grading, mahasiswa grade view |
| [features/conferences.md](features/conferences.md) | `Conference` model, self-hosted Jitsi via HS256 JWT, `JitsiTokenService::mint`, room lifecycle, per-role behaviour, room view (new-tab launcher), copy fan-out |
| [features/notifications.md](features/notifications.md) | `database` + `webpush` channels, notification catalog, dispatch sites, queue behaviour, payload shapes, WebPush subscription flow, admin debug |
| [features/discussions.md](features/discussions.md) | Models (`Discussion.content`, `DiscussionComment.content`), routes, sidebar cache, the lone Livewire component, Livewire 4 quirks |
| [features/announcements.md](features/announcements.md) | Shared resource (auth-only group), target_audience enum (`all/dosen/mahasiswa/specific`), attachments, per-role visibility gate, `AnnouncementNotification` dispatch |
| [features/admin-akademik.md](features/admin-akademik.md) | Unified `/admin/akademik` page, `AkademikController` sub-actions for years/semesters/departments/study programs/kelas/courses, kelas assign/unassign, legacy resource routes, admin users CRUD |
| [features/grades.md](features/grades.md) | Three grade views — admin filter list, dosen grade book + CSV export + quick-grade, mahasiswa per-course score view |
| [features/code-execution.md](features/code-execution.md) | `POST /execute-code` proxy, language allowlist + Piston mapping, bundled docker container, request/response shape, throttle + error handling, client-side iframe runtimes |
| [features/profile.md](features/profile.md) | Self-service `/profile` CRUD, `ProfileUpdateRequest` (one of two FormRequests), password + delete-account flows, deletion cascade caveats |
| [features/schedule.md](features/schedule.md) | `/mahasiswa/jadwal` — day-by-day timeline, what's shown, urgency colouring, convention exception (Eloquent relation, not raw query) |

## Operations

| Doc | What it covers |
|---|---|
| [ops/jitsi-self-host.md](ops/jitsi-self-host.md) | Full runbook for provisioning self-hosted Jitsi on the GCP VM — DNS, firewall, Caddy, `docker-jitsi-meet`, JWT secret, verification, rollback |

## Source / specialised

| Doc | What it covers |
|---|---|
| [bab4-implementasi.md](bab4-implementasi.md) | Thesis-style fact inventory (Indonesian) — implementation chapter source material for academic writeups. Not part of the daily dev docs. |

---

## Pointers within the repo (not under docs/)

- [CLAUDE.md](../CLAUDE.md) — concise project guide used as agent memory; mirrors the high-level architecture and gotchas in this doc set
- [PAGES_GUIDE.md](../PAGES_GUIDE.md) — page-by-page reference for the per-role UI screens
- `routes/web.php` + `routes/auth.php` — single source of truth for URL → controller mapping
- `app/Notifications/` — notification class catalogue
- `database/migrations/` — schema timeline
- `config/code_execution.php`, `config/services.php` — Piston languages, Jitsi env keys
