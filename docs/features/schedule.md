# Mahasiswa Schedule

## Overview

`GET /mahasiswa/jadwal` → `mahasiswa.schedule.index` — a day-by-day timeline of upcoming conferences and assignment deadlines for all enrolled courses.

Controller: `app/Http/Controllers/Mahasiswa/ScheduleController.php`
View: `resources/views/mahasiswa/schedule/index.blade.php`

---

## What It Shows

- **Conferences**: `live` status or `scheduled_at >= today` — no past conferences
- **Assignments**: open deadlines (`deadline >= today`) + assignments with no deadline
- **Window**: next 14 days shown first; any days beyond 14 that have events are appended in order

---

## Data Loading

```php
// Enrolled course IDs via Eloquent relation — exception to the raw-query convention
$enrolledCourseIds = $mahasiswa->enrollments()->pluck('courses.id');
```

Conferences are eager-loaded with `course` and `dosen`. Assignments are eager-loaded with `course`. Submitted assignment IDs are fetched in a single query and passed as a flat Collection for fast `.contains()` checks in the view.

---

## View Variables

| Variable | Type | Contents |
|---|---|---|
| `$days` | `Collection<string>` | Ordered date strings `Y-m-d`, 14-day window + extras |
| `$conferencesByDate` | `Collection<string, Collection<Conference>>` | Keyed by `Y-m-d` |
| `$assignmentsByDate` | `Collection<string, Collection<Assignment>>` | Keyed by `Y-m-d`; null-deadline entries under key `'no-deadline'` |
| `$submittedAssignmentIds` | `Collection<int>` | Assignment IDs the student has already submitted |

---

## Urgency Coloring

The view computes urgency inline per assignment:

| Condition | Label | Color |
|---|---|---|
| `< 0 h` (past) | `lewat` | error |
| `< 48 h` | `urgent` | error |
| `< 168 h` (7 days) | `soon` | tertiary |
| `>= 168 h` | `ok` | neutral |

Submitted assignments show a strikethrough title and "Sudah Submit" badge regardless of urgency.

---

## Convention Exception

This controller uses `$mahasiswa->enrollments()->pluck('courses.id')` via the Eloquent relation on `User`, while all other mahasiswa controllers use `DB::table('enrollments')` raw queries. If you rename a column in the `enrollments` table, grep for both patterns:

```bash
grep -r "DB::table('enrollments'" app/
grep -r "enrollments()->pluck" app/
```
