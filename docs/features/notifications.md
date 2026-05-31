# Notifications

## Channels

Every user-facing notification uses one or both of:

- **`database`** — stored in the `notifications` table (Laravel's UUID-PK schema). Read through `auth()->user()->notifications` / `unreadNotifications`. The bell badge in the layout shows unread count.
- **`webpush`** — sent via VAPID to subscribed browsers. Requires `VAPID_PUBLIC_KEY` + `VAPID_PRIVATE_KEY`. Subscriptions live in `push_subscriptions` and are managed by the `laravel-notification-channels/webpush` package + the `HasPushSubscriptions` trait on `User`.

A small number of notifications use `mail` instead (registration OTP, password reset).

There is no Pusher / Reverb / Soketi — `BROADCAST_CONNECTION=log` writes broadcast events to the log file and nothing else. Do not call `broadcast()` from new code.

---

## Notification classes

`app/Notifications/`:

| Class | Channels | Triggered when |
|---|---|---|
| `AcademicUpdateNotification` | `database`, `webpush` | Material/Assignment/Conference created or updated; mahasiswa added to a group |
| `AnnouncementNotification` | `database` | Announcement created (`AnnouncementController::store`) |
| `GradeNotification` | `database`, `webpush` | Dosen grades a submission (individual or group) |
| `SubmissionNotification` | `database` | Mahasiswa submits a tugas (notifies the course's dosen) |
| `TestNotification` | `database`, `webpush` | Admin manual trigger from `/admin/debug/push/send` |
| `OtpVerificationNotification` | `mail` | Registration + OTP resend |
| `ResetPasswordNotification` | `mail` (Laravel default) | Password reset link request |

All seven implement `ShouldQueue` and use the `Queueable` trait.

> **There is no `NotificationService` class.** A previous helper at `app/Services/NotificationService.php` has been removed; controllers now dispatch via the `Notification` facade directly: `Notification::send($users, new XxxNotification(...))` or via the `Notifiable` trait: `$user->notify(new XxxNotification(...))`.

---

## Dispatch sites

Use the facade for collections and the trait for single users:

```php
use Illuminate\Support\Facades\Notification;

// Many recipients
Notification::send($students, new AcademicUpdateNotification($title, $message, $url));

// Single user
$user->notify(new OtpVerificationNotification($code));
```

Where each notification fires in the code:

| Notification | Site |
|---|---|
| `AcademicUpdateNotification` | `Dosen\MaterialController::store/update`, `Dosen\AssignmentController::store/update`, `Dosen\ConferenceController::store/update`, `Mahasiswa\SubmissionController::store` (to other group members) |
| `AnnouncementNotification` | `AnnouncementController::store` |
| `GradeNotification` | `Dosen\AssignmentController::grade`, `Dosen\AssignmentController::gradeGroup` |
| `SubmissionNotification` | `Mahasiswa\SubmissionController::store` (to course dosen) |
| `TestNotification` | `Admin\PushDebugController::send` |
| `OtpVerificationNotification` | `Auth\RegisteredUserController::store`, `Auth\OtpVerificationController::resend` |
| `ResetPasswordNotification` | `User::sendPasswordResetNotification` (override of Breeze default) |

---

## Queue behaviour

All notifications are `ShouldQueue`. Behaviour depends on `QUEUE_CONNECTION`:

| `QUEUE_CONNECTION` | What happens |
|---|---|
| `sync` (default in `.env.example`) | Notifications dispatch inline within the request. A fan-out to many users will block the response until each is delivered. |
| `database` (recommended for prod) | Notifications are pushed onto the `jobs` table; a queue worker (`php artisan queue:listen` or `queue:work`) picks them up. **Without a worker, notifications never deliver.** |

Switching to `database` requires the worker to be running, otherwise the queue silently grows and users never see notifications. See [deployment.md](../deployment.md#queue-worker) for Supervisor config.

`composer dev` runs `php artisan queue:listen` as one of the concurrent dev processes, so local dev is fine on either driver.

---

## Notification payloads

### `toArray()` shape

The database channel stores the result of `toArray($notifiable)` as JSON in `notifications.data`. Every class uses the same shape:

```json
{
  "title": "…",
  "message": "…",
  "url": "https://…",      // where the bell-redirect should go
  "type": "academic_update | grade | submission | announcement | test"
}
```

`NotificationController::readAndRedirect` (`GET /notifications/{id}/redirect`) marks the notification as read and redirects to `data.url` if present. The bell entry in the topbar uses this endpoint.

### `toWebPush()` shape

For classes that include `WebPushChannel::class`:

```php
return (new WebPushMessage)
    ->title($this->title)
    ->icon('/logo.png')
    ->body($this->message)
    ->action('Buka', $this->actionUrl)
    ->data(['url' => $this->actionUrl])
    ->options(['TTL' => 1000]);
```

`TTL: 1000` means the push service can hold the notification for ~16 minutes before dropping. The browser-side service worker (`public/sw.js`) handles the `push` event and renders the OS notification; clicking it navigates to `data.url`.

### `toMail()` shape

`OtpVerificationNotification`:

```php
return (new MailMessage)
    ->subject('Kode Verifikasi Email - ' . config('app.name'))
    ->greeting('Halo, ' . $notifiable->name . '!')
    ->line('Terima kasih telah mendaftar di ' . config('app.name') . '.')
    ->line('Gunakan kode berikut untuk memverifikasi alamat email Anda:')
    ->line('# ' . $this->code)
    ->line('Kode ini akan **kadaluarsa dalam 10 menit**.')
    ->line('Jika Anda tidak melakukan pendaftaran, abaikan email ini.')
    ->salutation('Salam, ' . config('app.name'));
```

`ResetPasswordNotification` inherits Laravel's `Illuminate\Auth\Notifications\ResetPassword` template with an Indonesian override.

---

## WebPush subscription flow

```
Browser:
  navigator.serviceWorker.register('/sw.js')
    └── Notification.requestPermission()  → 'granted'
         └── registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: vapid_public })
              └── fetch POST /push-subscribe { endpoint, keys: { auth, p256dh } }

Server:
  PushSubscriptionController::store
    └── validate endpoint + keys
    └── $user->updatePushSubscription($endpoint, $key, $auth_token)   // HasPushSubscriptions trait
```

Subscription row in `push_subscriptions`:

| Column | Meaning |
|---|---|
| `subscribable_type` / `subscribable_id` | morph → `App\Models\User` + id |
| `endpoint` | unique string(500), the push service URL |
| `public_key` | client `p256dh` key |
| `auth_token` | client `auth` token |
| `content_encoding` | nullable |

Unsubscribe:

```
POST /push-unsubscribe { endpoint }
  → PushSubscriptionController::destroy
    → $user->deletePushSubscription($endpoint)
```

The layout's inline service-worker bootstrap subscribes automatically on every page load if `VAPID_PUBLIC_KEY` is set and permission is granted. Re-subscribing the same endpoint is idempotent because of the `endpoint` unique constraint.

---

## Admin debug / manual push

`Admin\PushDebugController` powers `/admin/debug/push`:

| Route | Action |
|---|---|
| `GET /admin/debug/push` | list users with their push subscriptions |
| `POST /admin/debug/push/send` | send a `TestNotification($title, $message)` to a chosen `user_id` |

Useful for verifying VAPID setup in staging before relying on the announcement / grade dispatch paths. `TestNotification` itself uses `database` + `webpush` (and does **not** implement `ShouldQueue` — it's the only exception).

---

## Sidebar / topbar count

The bell in `layouts/notifications.blade.php` reads `auth()->user()->unreadNotifications` (database channel). Marking-read happens through:

- `POST /notifications/mark-all-read` (`NotificationController::markAllRead`)
- `POST /notifications/{id}/mark-read` (`NotificationController::markRead`)
- `GET /notifications/{id}/redirect` (`NotificationController::readAndRedirect`) — marks read AND redirects to the stored `data.url`

The full notification list is at `GET /notifications` (`NotificationController::index`), paginated 15 per page.

---

## Adding a new notification

1. Create `app/Notifications/YourNotification.php` extending `Illuminate\Notifications\Notification`. Implement `ShouldQueue` (match the rest of the suite).
2. Implement `via($notifiable)`, `toArray($notifiable)`, and optionally `toWebPush($notifiable, $notification)` / `toMail($notifiable)`.
3. Use the standard `toArray` shape so the bell/redirect plumbing works.
4. Dispatch via `Notification::send(...)` or `$user->notify(...)` from the controller.
5. If the notification fans out to many users, mention it in the PR — sync queue + large recipient list = slow request.

Refer to `AcademicUpdateNotification` as the canonical example.
