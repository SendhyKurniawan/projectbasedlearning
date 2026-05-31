# Conferences

## The model

`Conference` belongs to a `Course` and to a creating `dosen` (User). Columns:

| Column | Notes |
|---|---|
| `id` | PK |
| `course_id` | FK courses cascade |
| `dosen_id` | FK users cascade — who created/owns the session |
| `title` | string |
| `description` | text nullable |
| `room_name` | string **unique** — used as the Jitsi room identifier |
| `scheduled_at` | datetime |
| `ended_at` | datetime nullable |
| `status` | enum `scheduled\|live\|ended`, default `scheduled` |
| timestamps | |

`room_name` is generated as `room-{course_id}-{Str::uuid()}` at create time, which keeps the global unique constraint satisfied even across courses.

Status values are exactly `scheduled`, `live`, `ended`. There is no `ongoing`. `Conference::isLive()` and `Conference::isEnded()` are helpers.

---

## Conferencing stack — self-hosted Jitsi via HS256 JWT

There is no LiveKit, no Jitsi-as-a-Service (JaaS), no embedded iframe. Conferences point at a self-hosted Jitsi at `JITSI_DOMAIN` and use HS256-signed JWTs minted by `App\Services\JitsiTokenService::mint()`. The room view renders a button that opens `https://{JITSI_DOMAIN}/{room_name}?jwt={jwt}` in a new tab. The browser hands the JWT to Jitsi over the standard `?jwt=` query param.

This is the result of a recent migration from JaaS (RSA / 8x8.vc / tenant-prefixed rooms) to self-hosted (HS256 / direct domain). See the commit history (`db5378a refactor(conferences): drop iframe, use standalone Jitsi tab launcher`, `099a07e refactor(conferences): migrate from Jitsi JaaS to self-hosted (HS256 JWT)`).

### `JitsiTokenService::mint($room, $userId, $name, $moderator, $email)`

```php
$now = time();
$payload = [
    'aud' => $appId,                      // JITSI_JWT_APP_ID
    'iss' => $appId,                      // same
    'sub' => $domain,                     // JITSI_DOMAIN
    'room' => $room,                      // conference.room_name
    'iat' => $now,
    'nbf' => $now - 10,
    'exp' => $now + 7200,                 // 2 hours
    'context' => [
        'user' => [
            'id' => (string) $userId,
            'name' => $name,
            'avatar' => '',
            'email' => $email ?? '',
            'moderator' => $moderator ? 'true' : 'false',
        ],
    ],
];

return JWT::encode($payload, $secret, 'HS256');
```

If `JITSI_JWT_APP_ID` or `JITSI_JWT_APP_SECRET` is missing, `mint()` throws `RuntimeException('Jitsi JWT credentials not configured…')` — every conference room view will then 500. Surface this in staging before prod.

`JITSI_JWT_APP_ID` and `JITSI_JWT_APP_SECRET` must equal the Jitsi server's `JWT_APP_ID` and `JWT_APP_SECRET`. Generate the secret with `openssl rand -hex 32` and never commit it.

The Jitsi server is provisioned per [ops/jitsi-self-host.md](../ops/jitsi-self-host.md).

---

## Room lifecycle

```
scheduled ──[dosen: start]──► live ──[dosen or admin: end]──► ended
```

- `scheduled` — created by the dosen via `conferences.store`. Visible in dosen + admin lists; mahasiswa sees it in their conference list but cannot join.
- `live` — set by `Dosen\ConferenceController::start` (`POST /dosen/conferences/{conference}/start`). Mahasiswa can now enter.
- `ended` — set by `Dosen\ConferenceController::end` or `Admin\ConferenceController::end`. Also sets `ended_at = now()`. Once ended, mahasiswa/dosen are redirected away.

The dosen "end" form returns either JSON (if AJAX/`wantsJson`) or redirect to the index with a success flash — the room view triggers it through an Alpine confirm modal that POSTs via a hidden form.

---

## Per-role behaviour

### Dosen (`Dosen\ConferenceController`)

| Route | Action |
|---|---|
| `GET /dosen/courses/{course}/conferences` | List active + ended (`conferences.index`) |
| `GET /dosen/courses/{course}/conferences/create` | `conferences.create` |
| `POST /dosen/courses/{course}/conferences` | `conferences.store` |
| `GET /dosen/conferences/{conference}/edit` | `conferences.edit` |
| `PUT /dosen/conferences/{conference}` | `conferences.update` |
| `DELETE /dosen/conferences/{conference}` | `conferences.destroy` |
| `POST /dosen/conferences/{conference}/copy` | `conferences.copy` |
| `POST /dosen/conferences/{conference}/start` | `conferences.start` (→ status=live) |
| `POST /dosen/conferences/{conference}/end` | `conferences.end` (→ status=ended, ended_at=now) |
| `GET /dosen/conferences/{conference}/room` | mint moderator JWT, render `dosen.conferences.room` |

The room view renders a "Buka Ruang Konferensi" link with `target="_blank" rel="noopener"` to `https://{domain}/{room_name}?jwt={jwt}`. JWT is minted with `moderator: true` so the dosen lands with the moderator toolbar. Below it is an "Akhiri Sesi" button that POSTs to `conferences.end` after a confirm.

Authorization: `$course->dosen_id === auth()->id()` (inline check in `authorizeDosen()`).

### Admin (`Admin\ConferenceController`)

| Route | Action |
|---|---|
| `GET /admin/conferences` | observer index of every active + ended conference (paginated) |
| `GET /admin/conferences/{conference}/room` | mint moderator JWT (admin gets moderator too), render `admin.conferences.room` |
| `POST /admin/conferences/{conference}/end` | force-end a session |

Admin is named `Name (Admin)` in the JWT context so dosen/mahasiswa can see who the admin is in the room. Admin's `room` action requires the conference to be `live` (redirects to index with error if not).

### Mahasiswa (`Mahasiswa\ConferenceController`)

| Route | Action |
|---|---|
| `GET /mahasiswa/courses/{course}/conferences` | list — ordered with `live` first, then `scheduled`, then `ended`; ordered by `scheduled_at` within each group |
| `GET /mahasiswa/conferences/{conference}/room` | mint participant JWT (`moderator: false`), render `mahasiswa.conferences.room` |

The mahasiswa room view 403s / redirects if `!isLive()`. Enrollment is checked via `DB::table('enrollments')`.

---

## Room view (Blade)

Both dosen and mahasiswa room views build the meet URL inline:

```blade
@php
    $domain = config('services.jitsi.domain');
    $meetUrl = 'https://' . $domain . '/' . $conference->room_name . '?jwt=' . $jwt;
@endphp

<a href="{{ $meetUrl }}" target="_blank" rel="noopener">
    Buka Ruang Konferensi
</a>
```

The link opens Jitsi in a new tab, fully native (no iframe embedding). This avoids a stack of permission/Storage/iframe issues that plagued the embed approach. The footer reminds users that mobile clients (Jitsi Meet app) need to point at `JITSI_DOMAIN` in the app settings to join.

No JavaScript bundle is required for the room view — there is no `conference-jitsi.js`.

---

## Create / store

`Dosen\ConferenceController::store` validation:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'scheduled_at' => 'required|date|after:now',
    'sibling_ids' => 'nullable|array',
    'sibling_ids.*' => 'integer|exists:courses,id',
]);
```

Then:

1. Create the primary conference under `$course` with a generated `room-{course.id}-{uuid()}` `room_name` and `status=scheduled`.
2. `AcademicUpdateNotification` to enrolled mahasiswa of `$course` ("Jadwal Kelas Virtual Baru").
3. For each valid sibling: create its own conference (new uuid for room name) and notify the sibling's mahasiswa.

Security intersect identical to other fan-out paths — submitted `sibling_ids` are filtered through `$course->siblings()->pluck('id')`.

---

## Copy

`POST /dosen/conferences/{conference}/copy` accepts `sibling_ids` (required, array, min:1). After intersect:

```php
foreach (Course::whereIn('id', $targetIds)->get() as $sibling) {
    $sibling->conferences()->create([
        'title'        => $conference->title,
        'description'  => $conference->description,
        'scheduled_at' => $conference->scheduled_at,
        'dosen_id'     => auth()->id(),
        'room_name'    => 'room-' . $sibling->id . '-' . Str::uuid(),
        'status'       => 'scheduled',
    ]);
}
```

Each copy gets `status=scheduled`, a fresh `room_name`, and no `ended_at`. The copy endpoint does **not** dispatch notifications (the create endpoint does that on its own per-sibling path).

---

## Update / destroy

`update()` allows changing `title`, `description`, `scheduled_at` only — not `status`. Updating fires `AcademicUpdateNotification` ("Jadwal Kelas Virtual Diperbarui") to enrolled mahasiswa.

`destroy()` removes the row outright. Cascade on `course_id` and `dosen_id` is on delete; no per-conference soft-delete.

---

## Failure modes / edge cases

- **Missing JWT env vars** → `RuntimeException` from `JitsiTokenService::mint()`, response 500. Validate `JITSI_JWT_APP_ID` and `JITSI_JWT_APP_SECRET` in staging.
- **`JITSI_DOMAIN` wrong** → meet URL still renders, but the new tab will fail to connect. Verify DNS + Caddy + Jitsi web container.
- **Mahasiswa joins a `scheduled` conference** → controller redirects back to the index with `error` flash: *"Sesi konferensi ini belum dimulai atau sudah berakhir."*
- **Dosen reopens a room after `end`** → `Dosen\ConferenceController::room` checks `isEnded()` and redirects out with an error.
- **Admin force-ends a session** → `conferences.status='ended'`, `ended_at=now()`. The Jitsi room itself doesn't immediately kick anyone — moderators have to use the in-meeting "End Meeting for All". The DB-side end is a record-keeping action.
- **Token rejection by Jitsi** → wrong `JITSI_JWT_APP_SECRET` mismatch between app and server: Jitsi shows "invalid token" on the join page. Run the verification step in [ops/jitsi-self-host.md](../ops/jitsi-self-host.md#step-4--verification) to confirm.

---

## Related env

| Variable | Purpose |
|---|---|
| `JITSI_DOMAIN` | Public hostname of the self-hosted Jitsi (e.g. `meet.polimedia.pblworkspace.com`). |
| `JITSI_JWT_APP_ID` | Must equal Jitsi `JWT_APP_ID`. |
| `JITSI_JWT_APP_SECRET` | Must equal Jitsi `JWT_APP_SECRET`. 32-byte hex, never commit. |

Set in `config/services.php` as `services.jitsi.{domain,jwt_app_id,jwt_app_secret}`.
