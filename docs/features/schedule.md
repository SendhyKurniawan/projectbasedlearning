# Mahasiswa Schedule

## Overview

`GET /mahasiswa/jadwal` → `mahasiswa.schedule.index` — a day-by-day timeline of upcoming conferences and assignment deadlines across every enrolled course.

- **Controller**: `app/Http/Controllers/Mahasiswa/ScheduleController.php`
- **View**: `resources/views/mahasiswa/schedule/index.blade.php`
- **Route**: registered in `routes/web.php` under the `auth + role:mahasiswa` middleware group:

```php
Route::get('/jadwal', [Mahasiswa\ScheduleController::class, 'index'])->name('schedule.index');
```

---

## What it shows

- **Conferences** with `status='live'` OR `scheduled_at >= today`.
- **Assignments** with `deadline >= today` OR `deadline IS NULL` (null deadlines bucket under a special `'no-deadline'` key).
- **Window**: the next 14 calendar days are always rendered, plus any later days that still have events.

There is no past view — once a conference ends or an assignment deadline passes, it falls off the schedule. Use `/mahasiswa/grades` or the course-show page to find historic items.

---

## Data loading

```php
$enrolledCourseIds = $mahasiswa->enrollments()->pluck('courses.id');
```

This is the **convention exception**: the rest of the mahasiswa controllers use `DB::table('enrollments')->where(...)` raw queries (see [database.md](../database.md#enrollments)). `ScheduleController`, `SubmissionController`, and `DashboardController` use the Eloquent relation. If you rename a column in `enrollments`, grep for both patterns.

```php
$conferences = Conference::whereIn('course_id', $enrolledCourseIds)
    ->where(function ($q) {
        $q->where('status', 'live')
          ->orWhere('scheduled_at', '>=', now()->startOfDay());
    })
    ->with(['course', 'dosen'])
    ->orderBy('scheduled_at')
    ->get();

$assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
    ->with('course')
    ->where(function ($q) {
        $q->whereNull('deadline')
          ->orWhere('deadline', '>=', now()->startOfDay());
    })
    ->orderByRaw('deadline IS NULL, deadline ASC')
    ->get();

$submittedAssignmentIds = $assignments->isNotEmpty()
    ? $mahasiswa->submissions()
        ->whereIn('assignment_id', $assignments->pluck('id'))
        ->pluck('assignment_id')
    : collect();
```

`orderByRaw('deadline IS NULL, deadline ASC')` puts null-deadline assignments at the bottom of any non-grouped list.

`$submittedAssignmentIds` is a flat collection of assignment IDs so the view can do a fast `.contains()` check per row.

---

## Day-bucketing

```php
$conferencesByDate = $conferences->groupBy(
    fn ($c) => $c->scheduled_at->format('Y-m-d')
);

$assignmentsByDate = $assignments->groupBy(
    fn ($a) => $a->deadline ? $a->deadline->format('Y-m-d') : 'no-deadline'
);

$days = collect();
for ($i = 0; $i < 14; $i++) {
    $days->push(now()->startOfDay()->addDays($i)->format('Y-m-d'));
}
$extraDays = $conferencesByDate->keys()
    ->merge($assignmentsByDate->keys()->filter(fn ($d) => $d !== 'no-deadline'))
    ->filter(fn ($d) => ! $days->contains($d))
    ->sort()
    ->values();
$days = $days->merge($extraDays);
```

So the view iterates a single ordered list of date strings (`Y-m-d`), looking up `$conferencesByDate[$day]` and `$assignmentsByDate[$day]`. The `'no-deadline'` bucket is rendered as a separate panel below the day-by-day grid.

---

## View variables

| Variable | Type | Contents |
|---|---|---|
| `$days` | `Collection<string>` | ordered date strings (14-day window + extras with events) |
| `$conferencesByDate` | `Collection<string, Collection<Conference>>` | keyed by `Y-m-d` |
| `$assignmentsByDate` | `Collection<string, Collection<Assignment>>` | keyed by `Y-m-d`; null-deadline entries under key `'no-deadline'` |
| `$submittedAssignmentIds` | `Collection<int>` | flat list for `.contains($id)` checks |

---

## Urgency colouring (rendered inline in the view)

The view computes per-assignment urgency from the time-to-deadline:

| Condition | Label | Colour |
|---|---|---|
| `now > deadline` | `lewat` | error |
| `0 < hours_until < 48` | `urgent` | error |
| `48 <= hours_until < 168` (7 days) | `soon` | tertiary |
| `>= 168` | `ok` | neutral |

Submitted assignments (`$submittedAssignmentIds->contains($a->id)`) show a strikethrough title and a "Sudah Submit" badge regardless of urgency.

---

## Conferences in the schedule

Conferences listed include `live` and future scheduled. Mahasiswa cannot join a `scheduled` one — clicking "Gabung" only works once the dosen flips it to `live` via `Dosen\ConferenceController::start`. See [conferences.md](conferences.md).

Each conference card links to `route('mahasiswa.conferences.room', $conference)` so the routing through the Jitsi launcher is consistent with the per-course conference list.

---

## Things this view does NOT include

- Quiz attempts already in progress (no `started_at` timestamps surfaced here).
- Exercise solve sessions.
- Discussion or announcement activity.
- Past items (use the grade view).
- Course-level reminders unrelated to a specific assignment/conference.

If you add any of those, mirror the day-bucket pattern so the view stays a single iteration over `$days`.
