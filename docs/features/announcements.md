# Announcements

## What it is

A shared communication channel for admin and dosen to broadcast messages to subsets of users (everyone, all dosen, all mahasiswa, or specific users). Mahasiswa cannot author announcements. The resource is registered under the shared auth-only middleware group — no role prefix in the URL.

```php
// routes/web.php (inside the auth-only group)
Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);
```

So URLs are `/announcements`, `/announcements/create`, `/announcements/{announcement}`, etc.

---

## Model — `App\Models\Announcement`

```php
protected $fillable = [
    'user_id',
    'title',
    'content',
    'target_audience',
    'attachment_path',
    'attachment_name',
    'attachment_mime',
];

public function author()      { return $this->belongsTo(User::class, 'user_id'); }
public function targetedUsers() { return $this->belongsToMany(User::class, 'announcement_user'); }

public function hasAttachment(): bool   { return !empty($this->attachment_path); }
public function attachmentIsImage(): bool { return $this->hasAttachment() && str_starts_with((string)$this->attachment_mime, 'image/'); }
public function attachmentIsPdf(): bool   { return $this->hasAttachment() && $this->attachment_mime === 'application/pdf'; }
```

### Schema

| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `user_id` | FK users cascade | author |
| `title` | string | |
| `content` | text | |
| `target_audience` | enum | `all\|dosen\|mahasiswa\|specific`, default `all` |
| `attachment_path` | string nullable | path on `public` disk under `announcements/…` |
| `attachment_name` | string nullable | original filename |
| `attachment_mime` | string(100) nullable | for rendering decision (image preview vs PDF link) |
| timestamps | | |

Plus the pivot `announcement_user` (for `target_audience='specific'`):

| Column | Notes |
|---|---|
| `announcement_id` | FK cascade |
| `user_id` | FK cascade |
| Unique `(announcement_id, user_id)` | prevents duplicates |

---

## Routes

| Method + URL | Action | Notes |
|---|---|---|
| `GET /announcements` | `index` | search + target filter, paginated 10/page |
| `GET /announcements/create` | `create` | admin or dosen only — 403 for mahasiswa |
| `POST /announcements` | `store` | author + audience + optional attachment |
| `GET /announcements/{announcement}` | `show` | per-role visibility gate (see below) |
| `GET /announcements/{announcement}/edit` | `edit` | author OR admin |
| `PUT /announcements/{announcement}` | `update` | author OR admin |
| `DELETE /announcements/{announcement}` | `destroy` | author OR admin |

---

## Index filter chain

`AnnouncementController::index` builds the query depending on the requester's role:

```php
$query = Announcement::with('author')->latest();

if ($user->role === 'mahasiswa') {
    $query->where(function ($q) use ($user) {
        $q->whereIn('target_audience', ['all', 'mahasiswa'])
          ->orWhereHas('targetedUsers', fn ($subq) => $subq->where('user_id', $user->id));
    });
} elseif ($user->role === 'dosen') {
    $query->where(function ($q) use ($user) {
        $q->whereIn('target_audience', ['all', 'dosen'])
          ->orWhere('user_id', $user->id)
          ->orWhereHas('targetedUsers', fn ($subq) => $subq->where('user_id', $user->id));
    });
}
// admin sees everything (no extra where)
```

Additional optional filters:

- `?search=` matches `title` OR `content` with `LIKE %…%`
- `?target=all|dosen|mahasiswa|specific` exact match

Results: `paginate(10)->withQueryString()` — filters persist across pagination links.

---

## Create / store

`create()` returns role-specific options:

| Role | Audience choices | User picker scope |
|---|---|---|
| admin | `all`, `dosen`, `mahasiswa`, `specific` | all users except self |
| dosen | `mahasiswa`, `specific` | mahasiswa only |
| mahasiswa | aborts 403 | — |

### Validation

```php
$rules = [
    'title' => 'required|string|max:255',
    'content' => 'required|string',
    'target_audience' => 'required|in:all,dosen,mahasiswa,specific',
    'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
];

if ($request->target_audience === 'specific') {
    $rules['specific_users'] = 'required|array';
    $rules['specific_users.*'] = 'exists:users,id';
}

// dosen extra check
if ($user->role === 'dosen' && !in_array($request->target_audience, ['mahasiswa', 'specific'])) {
    abort(403, 'Akses audiens ditolak.');
}
```

Attachment cap is 10 MB, MIME-restricted to common image formats + PDF. Files land at `announcements/{time()}_{name}` on the `public` disk; `attachment_name` keeps the original filename for the download link; `attachment_mime` drives whether the view shows an `<img>` or a download link.

### Notification dispatch

After saving, the controller materialises the recipient list and sends `AnnouncementNotification` (database channel only):

```php
$notifiableUsers = collect();
if ($validated['target_audience'] === 'all') {
    $notifiableUsers = User::where('id', '!=', $user->id)->get();
} elseif ($validated['target_audience'] === 'mahasiswa') {
    $notifiableUsers = User::where('role', 'mahasiswa')->get();
} elseif ($validated['target_audience'] === 'dosen') {
    $notifiableUsers = User::where('role', 'dosen')->where('id', '!=', $user->id)->get();
} elseif ($validated['target_audience'] === 'specific') {
    $notifiableUsers = User::whereIn('id', $validated['specific_users'])->get();
}

if ($notifiableUsers->isNotEmpty()) {
    Notification::send($notifiableUsers, new AnnouncementNotification(
        $announcement->id, $announcement->title, $user->name
    ));
}
```

`AnnouncementNotification` is `ShouldQueue` — see [notifications.md](notifications.md). For `target_audience='all'` against a large user base, this can be a slow request under the default `QUEUE_CONNECTION=sync`. Switch to `database` + a worker in production.

---

## Show

`AnnouncementController::show` enforces a per-role visibility check that mirrors the index filter:

```php
$isAllowed = false;

if ($user->role === 'admin' || $announcement->user_id === $user->id) {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'all') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'mahasiswa' && $user->role === 'mahasiswa') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'dosen' && $user->role === 'dosen') {
    $isAllowed = true;
} elseif ($announcement->target_audience === 'specific') {
    if ($announcement->targetedUsers()->where('user_id', $user->id)->exists()) {
        $isAllowed = true;
    }
}

if (!$isAllowed) {
    abort(403, 'Anda tidak memiliki hak akses untuk melihat pengumuman ini.');
}
```

A user who lands on `/announcements/{id}` via direct URL but isn't in the audience gets 403, even if they could read the title in their notification list.

---

## Edit / update / destroy

Only the original author OR admin can mutate an announcement:

```php
if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
    abort(403);
}
```

`update()` handles attachment replacement:

- `remove_attachment` boolean flag → delete the existing file, null the three attachment columns.
- New `attachment` file → delete the existing file, store the new one.

For `target_audience='specific'` the pivot is `sync()`'d to the new list. Switching away from `specific` `detach()`'es everything.

`destroy()` deletes the attachment file (if any) then the row. Cascade on `user_id` and on the `announcement_user` pivot keeps it clean.

---

## View payload (notification)

`AnnouncementNotification::toArray()`:

```json
{
  "title": "Pengumuman Baru",
  "message": "{Author Name} membuat pengumuman: {Announcement Title}",
  "url": "/announcements/{id}",
  "type": "announcement"
}
```

`NotificationController::readAndRedirect` (`GET /notifications/{id}/redirect`) marks read and follows `url`, which lands on the announcement show page — where the visibility gate runs as the final guard.

---

## What's intentionally missing

- **No WebPush channel** for announcements. The `AnnouncementNotification` class lists only `['database']` in `via()`. If you want push delivery, add `WebPushChannel::class` and a `toWebPush()` method (mirror `AcademicUpdateNotification`).
- **No edit history.** Updates overwrite the row in place. If you need an audit trail, add a `revisions` table or a model-events log.
- **No expiry / scheduled publish.** Announcements appear immediately. Add a `published_at` or `expires_at` column if you need scheduling.
