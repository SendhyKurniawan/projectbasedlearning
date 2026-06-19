# Memulai

## Prasyarat

- **PHP 8.2+** dengan ekstensi: `pdo_mysql`, `gd`, `zip`, `bcmath`, `intl`, `mbstring`, `xml`, `pdo_sqlite` (untuk test `RefreshDatabase` pada Pest)
- **Composer 2**
- **Node 20+** dengan NPM 10+
- **Docker** + Docker Compose (untuk jalur kontainer atau Piston / Mailhog / MySQL lokal)
- **Git**

---

## Jalur Docker (mirip produksi)

Jalur Docker memakai MySQL 8, Mailhog, dan sandbox eksekusi kode Piston bawaan — komposisi yang sama dengan yang berjalan di produksi di belakang Caddy.

```bash
git clone <repo> pjbl && cd pjbl
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Lalu:

- Aplikasi: `http://localhost:8000`
- phpMyAdmin: `http://localhost:8081`
- UI MailHog: `http://localhost:8025`

Entrypoint kontainer `app` menyalin `.env.example` → `.env` saat boot pertama (bila `.env` belum ada) dan menunggu healthcheck kontainer `db` sebelum menjalankan PHP-FPM.

> Port sisi host untuk aplikasi adalah `127.0.0.1:8000` (hanya terikat ke localhost). Di produksi, Caddy pada host mem-proxy HTTPS:443 → `127.0.0.1:8000`. Secara lokal Anda mengakses `http://localhost:8000` langsung.

### Vite saat pengembangan

Jalankan Vite di dalam kontainer atau native di host. Di dalam kontainer:

```bash
docker compose exec app npm install
docker compose exec app npm run dev
```

Bila nanti Anda menambah entry point Vite baru, **hapus volume `app_build` sebelum re-deploy** — volume itu membayangi build baru. Lihat [deployment.md](deployment.md#vite-assets-gotcha).

---

## Jalur native (tanpa Docker)

```bash
git clone <repo> pjbl && cd pjbl
composer setup        # install → .env → key:generate → migrate → npm install → npm run build → composer optimize
composer dev          # bersamaan: php artisan serve + queue:listen + pail + vite
```

`composer setup` menyalin `.env.example` ke `.env` bila belum ada, men-generate `APP_KEY`, menjalankan migrasi (force), memasang node modules, mem-build aset produksi, lalu meng-cache config/route/view/event.

`composer dev` menjalankan empat proses via `concurrently`:

| Nama | Perintah |
|---|---|
| `server` | `php artisan serve` (default `http://127.0.0.1:8000`) |
| `queue` | `php artisan queue:listen --tries=1 --timeout=0` |
| `logs` | `php artisan pail --timeout=0` (tail log real-time) |
| `vite` | `npm run dev` |

Untuk dev native, Anda perlu menjalankan MySQL lokal atau menimpa variabel database agar memakai SQLite. Setup SQLite cepat:

```bash
touch database/database.sqlite
# override di .env:
DB_CONNECTION=sqlite
DB_DATABASE=/path/absolut/ke/database/database.sqlite
# (atau kosongkan — Laravel akan memakai `database/database.sqlite` relatif bila nilai env diabaikan)
```

`.env.example` default disetel untuk MySQL dengan hostname Docker (`DB_HOST=db`, `DB_PORT=3306`). Saat berjalan native terhadap MySQL host, ubah menjadi `DB_HOST=127.0.0.1`.

---

## Variabel lingkungan

Referensi lengkap. Nilai yang ditampilkan adalah default `.env.example`.

### App

| Variabel | Default | Catatan |
|---|---|---|
| `APP_NAME` | `PBL Workspace` | tampil di UI, subjek email, judul push |
| `APP_ENV` | `local` | ganti ke `production` saat deploy |
| `APP_KEY` | _(kosong, jalankan `key:generate`)_ | |
| `APP_DEBUG` | `true` | **harus `false`** di produksi |
| `APP_URL` | `http://localhost:8000` | URL absolut termasuk skema; contoh prod `https://polimedia.pblworkspace.com` |
| `APP_LOCALE` | `id` | string UI berbahasa Indonesia; helper tanggal memakai locale Carbon |
| `APP_FALLBACK_LOCALE` | `en` | |
| `APP_FAKER_LOCALE` | `en_US` | factory |
| `APP_MAINTENANCE_DRIVER` | `file` | |
| `BCRYPT_ROUNDS` | `10` | |

### Logging

| Variabel | Default |
|---|---|
| `LOG_CHANNEL` | `stack` |
| `LOG_STACK` | `single` |
| `LOG_DEPRECATIONS_CHANNEL` | `null` |
| `LOG_LEVEL` | `debug` |

### Database

| Variabel | Default | Catatan |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Default MySQL agar selaras dengan Docker. Pakai `sqlite` untuk dev native tanpa setup. |
| `DB_HOST` | `db` | hostname service Docker; `127.0.0.1` untuk native |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `pjbl` | |
| `DB_USERNAME` | `pjbl` | |
| `DB_PASSWORD` | `password` | |

### Session / Cache / Queue / Broadcast / Filesystem

| Variabel | Default | Catatan |
|---|---|---|
| `SESSION_DRIVER` | `database` | `file` lebih cepat untuk lokal single-server; `redis` untuk multi-server |
| `SESSION_LIFETIME` | `120` (menit) | |
| `SESSION_ENCRYPT` | `false` | |
| `SESSION_PATH` | `/` | |
| `SESSION_DOMAIN` | `null` | produksi: set ke domain apex Anda (mis. `polimedia.pblworkspace.com`) |
| `CACHE_STORE` | `database` | ganti ke `redis` di prod bila Redis tersedia |
| `QUEUE_CONNECTION` | `sync` | dispatch inline; ganti ke `database` di prod dan jalankan worker |
| `BROADCAST_CONNECTION` | `log` | tanpa Pusher/Reverb. Jangan panggil `broadcast()` tanpa menyiapkan driver dulu |
| `FILESYSTEM_DISK` | `local` | konten unggahan lewat disk `public` (`storage/app/public`) — jalankan `php artisan storage:link` sekali |

### Mail

| Variabel | Default | Catatan |
|---|---|---|
| `MAIL_MAILER` | `smtp` | |
| `MAIL_HOST` | `mailhog` | hostname Docker kontainer Mailhog; native: `127.0.0.1` |
| `MAIL_PORT` | `1025` | port SMTP Mailhog |
| `MAIL_USERNAME` | `null` | |
| `MAIL_PASSWORD` | `null` | |
| `MAIL_ENCRYPTION` | `null` | |
| `MAIL_FROM_ADDRESS` | `noreply@pbl.test` | |
| `MAIL_FROM_NAME` | `${APP_NAME}` | |

GCP memblokir port keluar 25; untuk produksi gunakan relay (Resend / Brevo / Postmark / Mailgun / SES).

### AWS (opsional, hanya bila `FILESYSTEM_DISK` diganti ke S3)

| Variabel | Default |
|---|---|
| `AWS_ACCESS_KEY_ID` | _(kosong)_ |
| `AWS_SECRET_ACCESS_KEY` | _(kosong)_ |
| `AWS_DEFAULT_REGION` | `us-east-1` |
| `AWS_BUCKET` | _(kosong)_ |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` |

### Jitsi (self-hosted)

| Variabel | Default | Catatan |
|---|---|---|
| `JITSI_DOMAIN` | `meet.polimedia.pblworkspace.com` | hostname publik; front-end membangun URL ruang sebagai `https://{domain}/{room_name}?jwt=…` |
| `JITSI_JWT_APP_ID` | _(kosong)_ | harus cocok dengan `JWT_APP_ID` server Jitsi (mis. `pjbl`). Wajib — `JitsiTokenService::mint()` melempar error tanpa ini. |
| `JITSI_JWT_APP_SECRET` | _(kosong)_ | harus cocok dengan `JWT_APP_SECRET` server Jitsi. Hex 32-byte; jangan pernah di-commit. |

Untuk pengembangan konferensi lokal saja tanpa server Jitsi sungguhan, set `JITSI_DOMAIN` ke apa pun ("meet.local"), serta `JITSI_JWT_APP_ID` + secret non-kosong apa pun. Token yang dihasilkan secara teknis valid; membuka tab meeting hanya akan 404 kecuali Anda benar-benar mengarah ke server.

### Eksekusi kode (Piston)

| Variabel | Default | Catatan |
|---|---|---|
| `PISTON_URL` | `http://piston:2000/api/v2` | mengarah ke kontainer `piston` bawaan di Docker; untuk dev native pakai `https://emkc.org/api/v2/piston` |
| `PISTON_TIMEOUT` | `10` | detik sebelum proxy mengembalikan `502 Execution service unavailable.` |

Bahasa yang diizinkan (`config/code_execution.php`): `java`, `php`, `csharp` (Piston menjalankan C# sebagai `csharp.net`).

### WebPush (VAPID)

| Variabel | Default | Catatan |
|---|---|---|
| `VAPID_PUBLIC_KEY` | _(kosong)_ | wajib untuk push notification. Generate dengan `php artisan webpush:vapid`. |
| `VAPID_PRIVATE_KEY` | _(kosong)_ | perintah yang sama. |
| `VAPID_SUBJECT` | `mailto:admin@example.com` | email kontak untuk layanan push. |

Bila `VAPID_PUBLIC_KEY` kosong, layout diam-diam melewati proses subscribe browser — push tidak berfungsi sampai diisi.

---

## Akun hasil seed default

`DatabaseSeeder` berjalan (berurutan): `AcademicYearSeeder`, `SemesterSeeder`, `DepartmentSeeder`, `StudyProgramSeeder`, `StudentClassSeeder`, `UserSeeder`, `CourseSeeder`, `DummyDataSeeder`, `AssignmentSeeder`.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@pjbl.test` | `password` |
| Dosen | `dosen@pjbl.test` | `password` |
| Dosen | `budi.dosen@pjbl.test` | `password` |
| Dosen | `siti.dosen@pjbl.test` | `password` |
| Mahasiswa | `mahasiswa@pjbl.test` | `password` (`student_class_id=1`) |
| Mahasiswa | `ahmad.mhs@pjbl.test`, `dewi.mhs@…`, `cahya.mhs@…`, `rina.mhs@…`, `fajar.mhs@…` | `password` (`student_class_id` acak ∈ {1,2}) |

Login admin ada di `/admin/login` (controller Breeze terpisah); role lain login di `/login`.

---

## Perintah harian

```bash
composer dev          # jalankan dev server (serve + queue + pail + vite)
composer test         # jalankan suite Pest (membersihkan cache config dulu)
vendor/bin/pint       # rapikan gaya kode PHP
npm run build         # build aset produksi
npm run dev           # vite saja (HMR)
npm run audit:contrast  # cek kontras WCAG pada palet design token

php artisan migrate:fresh --seed     # reset DB + seed ulang
php artisan storage:link             # tautkan storage publik ke storage/app/public
php artisan webpush:vapid            # generate kunci VAPID (tulis ke .env)
```

Varian Docker:

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan tinker
docker compose logs -f app
```

---

## Troubleshooting awal

- **`SQLSTATE[HY000] [2002]`** — MySQL belum siap atau hostname salah. Di dalam docker host harus `db`; di luar pakai `127.0.0.1` dengan port di-forward.
- **`Vite manifest not found at: public/build/manifest.json`** — Jalankan `npm run build` (prod) atau `npm run dev` (HMR).
- **Push notification tidak pernah datang** — `VAPID_PUBLIC_KEY` kosong, `Notification.requestPermission()` diblokir, atau service worker (`/sw.js`) gagal terdaftar. Cek konsol browser.
- **Halaman konferensi 500** — `JITSI_JWT_APP_ID` atau `JITSI_JWT_APP_SECRET` hilang. `JitsiTokenService::mint()` melempar `RuntimeException` sebelum merender.
- **"Run" pada exercise mengembalikan 502** — kontainer Piston tidak berjalan, atau `PISTON_URL` tak terjangkau. Di dalam docker: `docker compose ps piston`.
- **Login OK tapi terjebak di layar login** — Akun memiliki `is_active=false`. Mahasiswa dengan OTP belum terverifikasi diarahkan ke `/verify-otp`; dosen yang menunggu persetujuan admin melihat error "Akun belum diaktifkan".

Lihat [auth-roles.md](auth-roles.md) untuk alur registrasi/OTP/persetujuan admin.
