# Deployment

## Topologi produksi

```
Browser
  └── kontainer pjbl-caddy (TLS, Let's Encrypt, port 80/443 + 443/udp HTTP/3)
        ├── reverse_proxy pjbl-web:80   → kontainer Nginx → pjbl-app:9000 (PHP-FPM)
        └── reverse_proxy jitsi-web:80  → kontainer web Jitsi (Jitsi self-hosted)

  └── kontainer pjbl-coturn (host networking) → relay TURN UDP/TCP 3478 untuk NAT traversal Jitsi
  └── kontainer jitsi-meet-jvb-1 → media UDP 10000 (langsung, bukan via Caddy)
```

Semuanya berjalan di VM GCP `pjbl-vm` (project `pjbl-app-btgs6`, zona `asia-southeast2-a`, IP `34.50.107.24`) — sebuah `e2-standard-4` (4 vCPU / 16 GB) di **Debian 12 (bookworm)**. Lihat [ops/jitsi-self-host.md](ops/jitsi-self-host.md) untuk runbook provisioning lengkap.

**Caddy berjalan sebagai kontainer Docker** (`pjbl-caddy`, `caddy:2-alpine`), didefinisikan di `docker-compose.override.yml` dan bergabung ke `pjbl-network`. Ia menerminasi TLS dan mem-reverse-proxy **berdasarkan nama kontainer** (`pjbl-web:80`, `jitsi-web:80`) — bukan port host `127.0.0.1`. Port host `127.0.0.1:8000` (nginx aplikasi) dan `127.0.0.1:8080` (web jitsi) masih ada untuk debugging lokal, tetapi lalu lintas produksi mengalir sepenuhnya lewat jaringan docker. Caddy menyajikan tiga host:

- `polimedia.pblworkspace.com` → `pjbl-web:80` (host aplikasi kanonik)
- `meet.polimedia.pblworkspace.com` → `jitsi-web:80` (Jitsi)
- `pbl.kurniawansendhy.site` → `pjbl-web:80` (host lama, dipertahankan demi kontinuitas)

Jitsi adalah **proyek compose terpisah** (`~/jitsi-meet`, `jitsi/*:stable-9909`); kontainer `web`-nya bergabung ke `pjbl_pjbl-network` (external) aplikasi sehingga Caddy dapat me-resolve `jitsi-web` berdasarkan nama.

---

## Service Docker

`docker-compose.yml` dasar mendefinisikan stack aplikasi; `docker-compose.override.yml` menambah `caddy` dan `coturn` (override inilah yang menjadikannya deployment produksi alih-alih stack dev polos).

| Service | Kontainer | Image | Port terikat | Tujuan |
|---|---|---|---|---|
| `app` | `pjbl-app` | `pjbl-app` (di-build dari `Dockerfile`) | `9000` internal | Aplikasi PHP-FPM |
| `web` | `pjbl-web` | `nginx:alpine` | `127.0.0.1:8000:80` | Nginx — Caddy mem-reverse-proxy ke `pjbl-web:80` |
| `db` | `pjbl-db` | `mysql:8.0` | internal saja | MySQL 8 (healthcheck `mysqladmin ping`) |
| `phpmyadmin` | `pjbl-phpmyadmin` | `phpmyadmin:latest` | `8081:80` | GUI DB |
| `mailhog` | `pjbl-mailhog` | `mailhog/mailhog` | `8025:8025` | Penangkap mail (dev saja; prod pakai Resend) |
| `piston` | `pjbl-piston` | `ghcr.io/engineer-man/piston` | internal saja | Sandbox eksekusi kode; `tmpfs` untuk `/piston/jobs`, volume persisten untuk `/piston/packages` |
| `caddy` *(override)* | `pjbl-caddy` | `caddy:2-alpine` | `80:80`, `443:443`, `443:443/udp` | Terminasi TLS + reverse proxy (HTTP/3 aktif) |
| `coturn` *(override)* | `pjbl-coturn` | `coturn/coturn:latest` | host networking, `3478` udp/tcp | Relay TURN/STUN untuk NAT traversal Jitsi |

Kontainer `app` me-mount proyek, plus volume bernama untuk `vendor/`, `node_modules/`, dan `public/build/` (lihat "Gotcha aset Vite" di bawah). Kontainer `web` berbagi mount proyek dan volume `app_build`. Caddy memakai volume bernama **external** `pjbl_caddy_data` (sertifikat yang diterbitkan) dan `pjbl_caddy_config`. coturn membaca `~/coturn/turnserver.conf` (mount read-only) dan memakai `network_mode: host` agar rentang port relay-nya tidak terjebak di balik NAT Docker.

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Kesehatan: `db` punya healthcheck `mysqladmin ping` (interval 5s, 10 retry). `app` menunggu `db: service_healthy` + `mailhog: service_started`.

### Gotcha aset Vite

Volume `app_build` di-mount di `/var/www/public/build` untuk `app` dan `web`. Setelah terisi, **menambah entry point baru atau rebuild akan masuk ke volume**, bukan menggantikannya. Setelah deploy penambahan JS:

```bash
docker compose down
docker volume rm pjbl_app_build
docker compose up -d
docker compose exec app npm run build
```

(Prefiks nama-proyek bergantung pada nama proyek compose; nama volume yang berjalan adalah `pjbl_app_build` untuk proyek default.)

---

## Konfigurasi Nginx (`docker/nginx/conf.d/app.conf`)

- Brotli + Gzip aktif untuk aset teks.
- Header cache panjang pada `/build/*` (fingerprint Vite) — `Cache-Control: immutable, max-age=31536000`.
- FastCGI ke `app:9000`.
- `try_files $uri $uri/ /index.php?$query_string` untuk routing Laravel.

---

## Konfigurasi Caddy (`./Caddyfile`, di-mount ke `pjbl-caddy`)

Caddyfile berada di samping `docker-compose.yml` dan di-bind-mount ke `/etc/caddy/Caddyfile` di dalam kontainer. Ia mem-proxy berdasarkan **nama kontainer** lewat jaringan docker:

```caddy
pbl.kurniawansendhy.site {
    encode gzip
    reverse_proxy pjbl-web:80
}

polimedia.pblworkspace.com {
    encode gzip
    reverse_proxy pjbl-web:80
}

meet.polimedia.pblworkspace.com {
    encode gzip
    reverse_proxy jitsi-web:80
}
```

Caddy memperoleh/memperbarui sertifikat Let's Encrypt otomatis (disimpan di volume `pjbl_caddy_data`). Reload setelah mengedit Caddyfile:

```bash
docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile
# atau restart kontainer:
docker compose restart caddy
```

---

## Checklist produksi

```bash
# 1. Env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://polimedia.pblworkspace.com

# 2. Isi secret yang wajib (lihat bagian env di bawah)

# 3. Migrasi
php artisan migrate --force

# 4. Cache semuanya
composer optimize         # config:cache + route:cache + view:cache + event:cache

# 5. Build aset
npm install
npm run build

# 6. Symlink storage (bila belum)
php artisan storage:link

# 7. Queue: prod saat ini memakai QUEUE_CONNECTION=sync (tanpa worker). Hanya tambah
#    worker tersupervisi bila beralih ke driver database (lihat bagian Queue).

# 8. Izin berkas
chmod -R 775 storage bootstrap/cache
```

**`composer optimize` dijalankan setelah setiap deploy.** State config-cache yang basi akan diam-diam menutupi perubahan `.env` — memantulkan queue worker tidak cukup; Anda perlu `php artisan config:clear` (atau `composer optimize` yang meng-cache ulang segar) dan restart proses.

---

## Variabel lingkungan (produksi)

Lihat [getting-started.md](getting-started.md) untuk referensi lengkap. Yang kritikal untuk produksi:

| Variabel | Wajib | Catatan |
|---|---|---|
| `APP_KEY` | ya | `php artisan key:generate` |
| `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…` | ya | `APP_URL` salah akan merusak URL route absolut di mail/webpush. |
| `SESSION_DOMAIN=polimedia.pblworkspace.com`, `SESSION_SECURE_COOKIE=true` | ya (TLS) | |
| `DB_*` | ya | MySQL (atau kompatibel) |
| `QUEUE_CONNECTION` | `sync` (prod saat ini) | Notifikasi dispatch inline — tidak ada worker. Ganti ke `database` + worker tersupervisi hanya bila fan-out saat request (mis. umumkan-ke-semua) melambat. |
| `BROADCAST_CONNECTION=log` | biarkan | tidak ada Pusher/Reverb terpasang |
| `MAIL_*` | ya | ganti dari Mailhog ke SMTP / relay nyata (lihat Mail) |
| `JITSI_DOMAIN`, `JITSI_JWT_APP_ID`, `JITSI_JWT_APP_SECRET` | ya untuk konferensi | `JitsiTokenService::mint()` melempar error bila ada yang hilang |
| `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` | ya untuk push | `php artisan webpush:vapid` untuk generate |
| `PISTON_URL`, `PISTON_TIMEOUT` | ya untuk Run exercise | default `http://piston:2000/api/v2` (memakai kontainer bawaan) |

---

## Provisioning: Jitsi (self-hosted)

Ruang konferensi berjalan di Jitsi self-hosted di `meet.polimedia.pblworkspace.com`, di belakang Caddy berkontainer pada VM yang sama. Langkah lengkap (DNS, firewall, Caddy, instalasi `docker-jitsi-meet`, pembangkitan secret JWT, redirect SSO, verifikasi end-to-end, rollback) ada di [ops/jitsi-self-host.md](ops/jitsi-self-host.md).

NAT traversal untuk peserta di balik jaringan restriktif melewati server TURN mandiri **`pjbl-coturn`** (`coturn/coturn:latest`, host networking, `~/coturn/turnserver.conf`, port `3478` udp/tcp). `.env` Jitsi mengarahkan `TURN_HOST=meet.polimedia.pblworkspace.com` / `TURN_PORT=3478` ke sana. Media JVB tetap mengalir langsung lewat **UDP 10000** saat jaringan mengizinkan; coturn adalah relay cadangan.

Setelah Jitsi berjalan dan Anda punya secret JWT dari `~/jitsi-meet/.env`:

1. `JITSI_DOMAIN=meet.polimedia.pblworkspace.com`
2. `JITSI_JWT_APP_ID=<JWT_APP_ID dari .env Jitsi>` (mis. `pjbl`)
3. `JITSI_JWT_APP_SECRET=<JWT_APP_SECRET dari .env Jitsi>` — hex 32-byte via `openssl rand -hex 32`. Jangan pernah di-commit.
4. `php artisan config:clear && php artisan config:cache`

`App\Services\JitsiTokenService::mint()` melempar `RuntimeException('Jitsi JWT credentials not configured…')` bila `JITSI_JWT_APP_ID` atau `JITSI_JWT_APP_SECRET` hilang — munculkan error itu di staging sebelum prod, atau setiap view ruang konferensi akan 500.

Masa berlaku JWT 2 jam, ditandatangani HS256. Klaim: `aud=iss=JITSI_JWT_APP_ID`, `sub=JITSI_DOMAIN`, `room=<conference.room_name>`, flag moderator di dalam `context.user.moderator` sebagai string (`'true'`/`'false'`).

---

## Provisioning: WebPush / VAPID

```bash
php artisan webpush:vapid
```

Salin kedua kunci ke `.env`:

```env
VAPID_PUBLIC_KEY=BB….
VAPID_PRIVATE_KEY=…
VAPID_SUBJECT=mailto:admin@pblworkspace.com
```

**Kunci harus stabil**. Memutarnya membatalkan setiap baris di `push_subscriptions` — setiap browser akan re-subscribe pada load halaman berikutnya (bootstrap service-worker inline layout memanggil `pushManager.subscribe()` otomatis), tetapi notifikasi yang sedang dalam perjalanan antara rotasi dan re-subscribe akan gagal terkirim.

`VAPID_PUBLIC_KEY` dibaca langsung dari `env()` di dalam `layouts/app.blade.php` — memanggil `php artisan config:cache` **tidak** membakukannya ke cache untuk template itu. Jangan andalkan `config:clear` untuk memunculkan kunci yang hilang; ia hanya akan kosong.

---

## Queue worker

**Produksi saat ini memakai `QUEUE_CONNECTION=sync` tanpa proses worker** — setiap notifikasi dispatch inline di dalam request yang memicunya. Ini setup paling sederhana dan inilah yang ter-deploy sekarang (`composer dev` secara lokal juga membiarkan default `sync`, jadi `php artisan queue:listen` di runner dev adalah no-op untuk notifikasi).

Trade-off-nya: satu dispatch yang fan-out ke banyak penerima (mis. pengumuman ke semua mahasiswa) berjalan sinkron dan memperlambat request itu. Untuk ukuran kelas saat ini hal ini dapat diterima.

**Bila nanti Anda beralih ke `QUEUE_CONNECTION=database`**, setiap notifikasi di `app/Notifications/` mengimplementasikan `ShouldQueue`, jadi Anda **harus** menjalankan worker tersupervisi atau **tidak ada notifikasi yang terkirim**:

```ini
# /etc/supervisor/conf.d/pjbl-queue.conf
[program:pjbl-queue]
command=php /var/www/artisan queue:listen --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/pjbl-queue.log
```

`reload supervisorctl` lalu `supervisorctl restart pjbl-queue`.

---

## Mail

**Produksi memakai Resend** (`smtp.resend.com:587`). GCP memblokir port 25, jadi SMTP keluar langsung dari VM tidak berfungsi — relay pihak ketiga wajib (Resend, Brevo, Postmark, Mailgun, atau SES semua bisa). Config prod saat ini:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_…
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@pblworkspace.com
MAIL_FROM_NAME="PBL Workspace"
```

Notifikasi channel-mail di aplikasi ini: `OtpVerificationNotification`, `ResetPasswordNotification`. Keduanya `ShouldQueue`.

---

## Penyimpanan berkas

`FILESYSTEM_DISK=local` secara default. Konten unggahan (berkas materi, pengumpulan, lampiran pengumuman, banner mata kuliah) disimpan pada disk `public` di bawah:

- `storage/app/public/materials/…`
- `storage/app/public/submissions/…`
- `storage/app/public/announcements/…`

`php artisan storage:link` membuat `public/storage` → `storage/app/public`. Jalankan ulang setelah deploy apa pun yang menghapus `public/`.

---

## Cadangan (Backup)

Repo tidak menyertakan strategi backup. Rekomendasi minimal di VM:

```bash
# Dump MySQL harian
docker compose exec -T db mysqldump -uroot -p"$DB_ROOT_PASSWORD" pjbl > /backups/pjbl_$(date +%F).sql

# Sinkronkan ke GCS
gsutil rsync -r /backups gs://pjbl-backups/
```

Juga snapshot disk VM lewat GCP Console setelah cutover Jitsi (lihat [ops/jitsi-self-host.md](ops/jitsi-self-host.md) Langkah 5).

---

## Rollback

- Revert aplikasi: `git revert <sha>` pada branch deploy, lalu `composer optimize` dan pantulkan PHP-FPM + queue worker.
- Database: pulihkan dari dump terbaru bila sebuah migrasi merusak data. **Jangan pernah** `php artisan migrate:rollback` membabi buta di prod — baca method `down()` dulu.
- Rollback Jitsi ke JaaS tidak lagi mungkin tanpa memperkenalkan kembali kode yang telah dihapus (dukungan JaaS, berkas kunci RSA di `storage/app/private/jaas-private-key.pk`, dan kunci env lama sudah tiada — lihat bagian cleanup di [ops/jitsi-self-host.md](ops/jitsi-self-host.md)). Bila perlu kembali, hadirkan penyedia konferensi terkelola dan perkenalkan kembali driver-nya di `JitsiTokenService` (atau service sejenis).

---

## Logging

`LOG_CHANNEL=stack`, `LOG_STACK=single`, `LOG_LEVEL=debug` secara default. Di produksi, naikkan ke `LOG_LEVEL=info` atau `warning` dan pertimbangkan beralih ke `daily` untuk rotasi:

```env
LOG_STACK=daily
LOG_LEVEL=info
```

Pail (`php artisan pail`) disertakan di `composer dev` untuk tail log lokal.
