# Notifications

## Channels

All notifications use two channels: `database` and `webpush`.

- **`database`**: stored in the `notifications` table (standard Laravel). Readable via the `/notifications` route (all roles). The nav bar badge shows unread count.
- **`webpush`**: sent via VAPID to subscribed browsers. Requires `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` to be set. Subscriptions are stored in `push_subscriptions`.

---

## Notification Classes

| Class | Triggered when |
|---|---|
| `AcademicUpdateNotification` | Admin creates/updates academic hierarchy (semester, class, etc.) |
| `GradeNotification` | Dosen grades a submission (`NotificationService::sendSubmissionGradedNotification`) |
| `SubmissionNotification` | Mahasiswa submits (`NotificationService::sendSubmissionNotification`) |
| `AnnouncementNotification` | Announcement is created/updated |

---

## `NotificationService`

`app/Services/NotificationService.php` — three public methods:

```php
// Called in Dosen\AssignmentController::grade
$this->notificationService->sendSubmissionGradedNotification($submission);

// Called in Mahasiswa\SubmissionController::store
$this->notificationService->sendSubmissionNotification($submission);

// Called in AnnouncementController
$this->notificationService->sendAnnouncementNotification($announcement, $users);
```

All three dispatch synchronously — they run inline in the request, not via the queue. This means a slow WebPush delivery will slow down the response. If you need to move them to the queue, add `ShouldQueue` to the notification class — but first ensure the queue worker is running in prod.

---

## Queue Behavior

**Only `ResetPasswordNotification` is `Queueable`**. Every other notification dispatches synchronously even though `QUEUE_CONNECTION=database` is set.

Adding `implements ShouldQueue` to a notification class is a behavior change that requires the queue worker to be running or notifications silently drop. Don't do this without also verifying the worker is running.

---

## WebPush Subscription Flow

1. Browser requests notification permission
2. JS subscribes via the Push API → POSTs to `POST /push-subscribe` → `PushSubscriptionController::store`
3. Subscription stored in `push_subscriptions` with `endpoint`, `public_key`, `auth_token`, `user_id`
4. On notification dispatch, `laravel-notification-channels/webpush` reads subscriptions for the target user and sends via VAPID

`POST /push-unsubscribe` → `PushSubscriptionController::destroy` removes the subscription. Mahasiswa side handles this in the notification settings UI.

`Admin\PushDebugController` at `/admin/debug/push` lets admin manually trigger test push notifications — useful for verifying VAPID setup in staging.

---

## `BROADCAST_CONNECTION=log`

There is no Pusher, no Reverb, no Soketi wired up. `BROADCAST_CONNECTION=log` writes broadcast events to the log file and does nothing else.

Do not reach for `broadcast()` in new code without first setting up a driver. WebPush is the real-time delivery mechanism for user-facing notifications.
