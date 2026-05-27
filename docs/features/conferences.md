# Conferences

## The Model

`Conference` belongs to a `Course`. Key columns:

| Column | Notes |
|---|---|
| `room_name` | string unique — used as the Jitsi room identifier |
| `title` | string |
| `description` | text nullable |
| `status` | `scheduled \| ongoing \| ended` |
| `scheduled_at` | datetime |
| `ended_at` | timestamp nullable |

`room_name` must be globally unique — Jitsi uses it as the room URL path. The default is generated from the course + title + timestamp at create time.

---

## Jitsi JWT Minting (self-hosted)

`App\Services\JitsiTokenService::mint(string $room, int $userId, string $name, bool $moderator, ?string $email)` generates an **HS256**-signed JWT against the self-hosted Jitsi at `JITSI_DOMAIN`.

**Required env vars** (all three must be set):

| Var | Example |
|---|---|
| `JITSI_DOMAIN` | `meet.polimedia.pblworkspace.com` |
| `JITSI_JWT_APP_ID` | `pjbl` |
| `JITSI_JWT_APP_SECRET` | (32-byte hex from `openssl rand -hex 32`) |

`JITSI_JWT_APP_ID` and `JITSI_JWT_APP_SECRET` must match the Jitsi server's `JWT_APP_ID` and `JWT_APP_SECRET`. If any var is missing, `mint()` throws `RuntimeException` and conference room views 500. Provision these in staging before prod — see [deployment.md](../deployment.md).

Token claims: `iss = aud = JITSI_JWT_APP_ID`, `sub = JITSI_DOMAIN`, `room = room_name`, `exp = now + 2h`, moderator flag in the `context.user` payload.

---

## Room Lifecycle

```
scheduled ──[dosen: start]──► ongoing ──[dosen: end]──► ended
```

- **Dosen** (`/dosen/conferences/{conference}/room`): joins as moderator. Can `start` (→ `ongoing`) and `end` (→ `ended`). JWT has `moderator: true`.
- **Admin** (`/admin/conferences/{conference}/room`): observer mode — can view the room but the "End" button in the admin UI POSTs to `Admin\ConferenceController::end`. JWT has `moderator: false`.
- **Mahasiswa** (`/mahasiswa/conferences/{conference}/room`): can only enter when `status === 'ongoing'`. Controller redirects or 403s if not ongoing. JWT has `moderator: false`.

---

## Room View

`conference-jitsi.js` embeds the Jitsi Meet External API:

```javascript
// JWT is passed from controller to view as $jwt, then to JS via window.JITSI_*
const api = new JitsiMeetExternalAPI(domain, {
    roomName: roomName,
    jwt: jwtToken,
    parentNode: document.getElementById('jitsi-mount'),
});
```

For self-hosted Jitsi the room name is plain (no `${appId}/` tenant prefix). The JWT is injected via `@json` in the Blade view — not hardcoded. The `conference-jitsi.js` bundle is only loaded on room views, and the External API script is fetched from `https://${JITSI_DOMAIN}/external_api.js`.

---

## Copy Fan-out

`POST /dosen/conferences/{conference}/copy` copies a conference to sibling kelas. The copy gets a new `room_name` (unique), `status = scheduled`, and no `ended_at`. The same sibling intersect security pattern applies — see [contributing.md](../contributing.md).
