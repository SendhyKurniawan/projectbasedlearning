# Notifikasi (Notifications)

## Channel

Setiap notifikasi yang menghadap pengguna memakai salah satu atau kedua:

- **`database`** — disimpan di tabel `notifications` (skema PK-UUID Laravel). Dibaca lewat `auth()->user()->notifications` / `unreadNotifications`. Badge lonceng di layout menampilkan jumlah belum dibaca.
- **`webpush`** — dikirim via VAPID ke browser yang ber-subscribe. Memerlukan `VAPID_PUBLIC_KEY` + `VAPID_PRIVATE_KEY`. Subscription berada di `push_subscriptions` dan dikelola paket `laravel-notification-channels/webpush` + trait `HasPushSubscriptions` pada `User`.

Sejumlah kecil notifikasi memakai `mail` (OTP registrasi, reset password).

Tidak ada Pusher / Reverb / Soketi — `BROADCAST_CONNECTION=log` menulis event broadcast ke berkas log dan tidak lebih. Jangan panggil `broadcast()` dari kode baru.

---

## Kelas notifikasi

`app/Notifications/`:

| Kelas | Channel | Dipicu saat |
|---|---|---|
| `AcademicUpdateNotification` | `database`, `webpush` | Material/Assignment/Conference dibuat atau diperbarui; mahasiswa ditambahkan ke kelompok |
| `AnnouncementNotification` | `database` | Pengumuman dibuat (`AnnouncementController::store`) |
| `GradeNotification` | `database`, `webpush` | Dosen menilai submission (individu atau kelompok) |
| `SubmissionNotification` | `database` | Mahasiswa mengumpulkan tugas (memberi tahu dosen mata kuliah) |
| `TestNotification` | `database`, `webpush` | Pemicu manual admin dari `/admin/debug/push/send` |
| `OtpVerificationNotification` | `mail` | Registrasi + kirim ulang OTP |
| `ResetPasswordNotification` | `mail` (default Laravel) | Permintaan tautan reset password |

Enam dari tujuh mengimplementasikan `ShouldQueue` dan memakai trait `Queueable`. Pengecualiannya adalah **`TestNotification`**, yang dikirim sinkron.

> **Tidak ada kelas `NotificationService`.** Helper sebelumnya di `app/Services/NotificationService.php` telah dihapus; controller kini men-dispatch via facade `Notification` langsung: `Notification::send($users, new XxxNotification(...))` atau via trait `Notifiable`: `$user->notify(new XxxNotification(...))`.

---

## Lokasi dispatch

Pakai facade untuk koleksi dan trait untuk satu user:

```php
use Illuminate\Support\Facades\Notification;

// Banyak penerima
Notification::send($students, new AcademicUpdateNotification($title, $message, $url));

// Satu user
$user->notify(new OtpVerificationNotification($code));
```

Di mana tiap notifikasi dipicu dalam kode:

| Notifikasi | Lokasi |
|---|---|
| `AcademicUpdateNotification` | `Dosen\MaterialController::store/update`, `Dosen\AssignmentController::store/update`, `Dosen\ConferenceController::store/update`, `Mahasiswa\SubmissionController::store` (ke anggota kelompok lain) |
| `AnnouncementNotification` | `AnnouncementController::store` |
| `GradeNotification` | `Dosen\AssignmentController::grade`, `Dosen\AssignmentController::gradeGroup` |
| `SubmissionNotification` | `Mahasiswa\SubmissionController::store` (ke dosen mata kuliah) |
| `TestNotification` | `Admin\PushDebugController::send` |
| `OtpVerificationNotification` | `Auth\RegisteredUserController::store`, `Auth\OtpVerificationController::resend` |
| `ResetPasswordNotification` | `User::sendPasswordResetNotification` (override default Breeze) |

---

## Perilaku queue

Notifikasi yang `ShouldQueue` berperilaku tergantung `QUEUE_CONNECTION`:

| `QUEUE_CONNECTION` | Yang terjadi |
|---|---|
| `sync` (default di `.env.example`) | Notifikasi dispatch inline dalam request. Fan-out ke banyak user akan memblokir respons sampai tiap penerima terkirim. |
| `database` (disarankan untuk prod) | Notifikasi didorong ke tabel `jobs`; queue worker (`php artisan queue:listen` atau `queue:work`) mengambilnya. **Tanpa worker, notifikasi tidak pernah terkirim.** |

Beralih ke `database` mewajibkan worker berjalan, jika tidak antrean diam-diam membengkak dan pengguna tak pernah melihat notifikasi. Lihat [deployment.md](../deployment.md#queue-worker) untuk config Supervisor.

`composer dev` menjalankan `php artisan queue:listen` sebagai salah satu proses dev konkuren, jadi dev lokal aman pada driver mana pun.

---

## Payload notifikasi

### Bentuk `toArray()`

Channel database menyimpan hasil `toArray($notifiable)` sebagai JSON di `notifications.data`. Setiap kelas memakai bentuk yang sama:

```json
{
  "title": "…",
  "message": "…",
  "url": "https://…",      // ke mana bell-redirect harus menuju
  "type": "academic_update | grade | submission | announcement | test"
}
```

`NotificationController::readAndRedirect` (`GET /notifications/{id}/redirect`) menandai notifikasi terbaca dan redirect ke `data.url` bila ada. Entri lonceng di topbar memakai endpoint ini.

### Bentuk `toWebPush()`

Untuk kelas yang menyertakan `WebPushChannel::class`:

```php
return (new WebPushMessage)
    ->title($this->title)
    ->icon('/logo.png')
    ->body($this->message)
    ->action('Buka', $this->actionUrl)
    ->data(['url' => $this->actionUrl])
    ->options(['TTL' => 1000]);
```

`TTL: 1000` berarti layanan push dapat menahan notifikasi ~16 menit sebelum membuangnya. Service worker sisi browser (`public/sw.js`) menangani event `push` dan merender notifikasi OS; mengkliknya menavigasi ke `data.url`.

### Bentuk `toMail()`

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

`ResetPasswordNotification` mewarisi template `Illuminate\Auth\Notifications\ResetPassword` Laravel dengan override berbahasa Indonesia.

---

## Alur subscription WebPush

```
Browser:
  navigator.serviceWorker.register('/sw.js')
    └── Notification.requestPermission()  → 'granted'
         └── registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: vapid_public })
              └── fetch POST /push-subscribe { endpoint, keys: { auth, p256dh } }

Server:
  PushSubscriptionController::store
    └── validasi endpoint + keys
    └── $user->updatePushSubscription($endpoint, $key, $auth_token)   // trait HasPushSubscriptions
```

Baris subscription di `push_subscriptions`:

| Kolom | Arti |
|---|---|
| `subscribable_type` / `subscribable_id` | morph → `App\Models\User` + id |
| `endpoint` | string(500) unik, URL layanan push |
| `public_key` | kunci `p256dh` klien |
| `auth_token` | token `auth` klien |
| `content_encoding` | nullable |

Berhenti berlangganan:

```
POST /push-unsubscribe { endpoint }
  → PushSubscriptionController::destroy
    → $user->deletePushSubscription($endpoint)
```

Bootstrap service-worker inline pada layout men-subscribe otomatis pada tiap load halaman bila `VAPID_PUBLIC_KEY` diset dan izin diberikan. Re-subscribe endpoint yang sama idempoten karena constraint unik `endpoint`.

---

## Debug admin / push manual

`Admin\PushDebugController` menggerakkan `/admin/debug/push`:

| Route | Aksi |
|---|---|
| `GET /admin/debug/push` | daftar user dengan subscription push mereka |
| `POST /admin/debug/push/send` | kirim `TestNotification($title, $message)` ke `user_id` terpilih |

Berguna untuk memverifikasi setup VAPID di staging sebelum mengandalkan jalur dispatch pengumuman / nilai. `TestNotification` sendiri memakai `database` + `webpush` (dan **tidak** mengimplementasikan `ShouldQueue` — satu-satunya pengecualian).

---

## Hitungan sidebar / topbar

Lonceng di `layouts/notifications.blade.php` membaca `auth()->user()->unreadNotifications` (channel database). Penandaan-terbaca terjadi lewat:

- `POST /notifications/mark-all-read` (`NotificationController::markAllRead`)
- `POST /notifications/{id}/mark-read` (`NotificationController::markRead`)
- `GET /notifications/{id}/redirect` (`NotificationController::readAndRedirect`) — menandai terbaca DAN redirect ke `data.url` tersimpan

Daftar notifikasi lengkap ada di `GET /notifications` (`NotificationController::index`), berpaginasi 15 per halaman.

---

## Menambah notifikasi baru

1. Buat `app/Notifications/YourNotification.php` yang meng-extend `Illuminate\Notifications\Notification`. Implementasikan `ShouldQueue` (selaras dengan sisanya).
2. Implementasikan `via($notifiable)`, `toArray($notifiable)`, dan opsional `toWebPush($notifiable, $notification)` / `toMail($notifiable)`.
3. Pakai bentuk `toArray` standar agar plumbing bell/redirect berfungsi.
4. Dispatch via `Notification::send(...)` atau `$user->notify(...)` dari controller.
5. Bila notifikasi fan-out ke banyak user, sebutkan di PR — queue sync + daftar penerima besar = request lambat.

Rujuk `AcademicUpdateNotification` sebagai contoh kanonik.
