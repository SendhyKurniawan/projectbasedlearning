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

## Jitsi JaaS JWT Minting

`App\Services\JaasTokenService::mint(User $user, Conference $conference, bool $isModerator)` generates an RS256-signed JWT.

**Required env vars** (all four must be set):

| Var | Example |
|---|---|
| `JITSI_DOMAIN` | `8x8.vc` |
| `JITSI_APP_ID` | `vpaas-magic-cookie-abc123` |
| `JITSI_KID` | `vpaas-magic-cookie-abc123/key-id` |
| `JITSI_PRIVATE_KEY_PATH` | `storage/app/private/jaas-private-key.pk` |

If any var is missing or the key file is unreadable, `mint()` throws `RuntimeException`. Conference room views will 500. Provision these in staging before prod — see [deployment.md](../deployment.md).

Token claims: `iss = chat`, `aud = jitsi`, `sub = JITSI_APP_ID`, `room = room_name`, `exp = now + 2h`, moderator flag in the `context.user` payload.

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
// JWT is passed from controller to view as $jwt, then to JS
const api = new JitsiMeetExternalAPI(domain, {
    roomName: `${appId}/${roomName}`,
    jwt: jwtToken,
    parentNode: document.getElementById('jitsi-container'),
});
```

The JWT is injected via a `<meta>` tag or `@json` in the Blade view — not hardcoded. The `conference-jitsi.js` bundle is only loaded on room views.

---

## Copy Fan-out

`POST /dosen/conferences/{conference}/copy` copies a conference to sibling kelas. The copy gets a new `room_name` (unique), `status = scheduled`, and no `ended_at`. The same sibling intersect security pattern applies — see [contributing.md](../contributing.md).
