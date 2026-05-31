# Deployment

## Production topology

```
Browser
  └── Caddy on host VM (TLS, Let's Encrypt, port 443)
        ├── reverse_proxy 127.0.0.1:8000  → Nginx container → app:9000 (PHP-FPM)
        └── reverse_proxy 127.0.0.1:8080  → Jitsi web container (self-hosted Jitsi)
```

Caddy is installed directly on the GCP VM (`pjbl-vm` in project `pjbl-app-btgs6`, IP `34.50.107.24`) — see [ops/jitsi-self-host.md](ops/jitsi-self-host.md) for the full provisioning runbook. The app runs through `docker compose` with the services described below; Caddy proxies HTTPS to the internally-bound containers.

---

## Docker services (`docker-compose.yml`)

| Service | Image | Bound port | Purpose |
|---|---|---|---|
| `app` | `pjbl-app` (built from `Dockerfile`) | — | PHP-FPM application |
| `web` | `nginx:alpine` | `127.0.0.1:8000:80` | Nginx — Caddy on host reverse-proxies to this |
| `db` | `mysql:8.0` | internal only | MySQL 8 |
| `phpmyadmin` | `phpmyadmin:latest` | `8081:80` | DB GUI |
| `mailhog` | `mailhog/mailhog` | `8025:8025` | Mail catcher (dev only) |
| `piston` | `ghcr.io/engineer-man/piston` | internal only | Code execution sandbox; `tmpfs` for `/piston/jobs`, persistent volume for `/piston/packages` |

The `app` container mounts the project, plus named volumes for `vendor/`, `node_modules/`, and `public/build/` (see "Vite assets gotcha" below). The `web` container shares the project mount and the `app_build` volume.

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Health: `db` has a `mysqladmin ping` healthcheck (interval 5s, 10 retries). `app` waits on `db: service_healthy` + `mailhog: service_started`.

### Vite assets gotcha

The `app_build` volume is mounted at `/var/www/public/build` for both `app` and `web`. Once it's populated, **adding new entry points or rebuilding will go into the volume**, not be replaced. After deploying JS additions:

```bash
docker compose down
docker volume rm pjbl_app_build
docker compose up -d
docker compose exec app npm run build
```

(Project-name prefix depends on compose project name; the running volume name is `pjbl_app_build` for the default project.)

---

## Nginx config (`docker/nginx/conf.d/app.conf`)

- Brotli + Gzip on for text assets.
- Long cache headers on `/build/*` (Vite fingerprints) — `Cache-Control: immutable, max-age=31536000`.
- FastCGI to `app:9000`.
- `try_files $uri $uri/ /index.php?$query_string` for Laravel routing.

---

## Caddy config (`/etc/caddy/Caddyfile`)

```caddy
polimedia.pblworkspace.com {
    reverse_proxy 127.0.0.1:8000
}

meet.polimedia.pblworkspace.com {
    reverse_proxy 127.0.0.1:8080
}
```

Caddy obtains/renews Let's Encrypt certs automatically. Reload with `sudo systemctl reload caddy`.

---

## Production checklist

```bash
# 1. Env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://polimedia.pblworkspace.com

# 2. Fill required secrets (see env section below)

# 3. Migrate
php artisan migrate --force

# 4. Cache everything
composer optimize         # config:cache + route:cache + view:cache + event:cache

# 5. Build assets
npm install
npm run build

# 6. Storage symlink (if not done)
php artisan storage:link

# 7. Queue worker as a supervised process (see Queue section)

# 8. File permissions
chmod -R 775 storage bootstrap/cache
```

**`composer optimize` runs after every deploy.** Config-cache stale state will silently mask `.env` changes — bouncing the queue worker isn't enough; you need `php artisan config:clear` (or `composer optimize` which re-caches afresh) and a process restart.

---

## Environment variables (production)

See [getting-started.md](getting-started.md) for the full reference. Production-critical ones:

| Variable | Required | Notes |
|---|---|---|
| `APP_KEY` | yes | `php artisan key:generate` |
| `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…` | yes | Wrong `APP_URL` will break absolute route URLs in mail/webpush. |
| `SESSION_DOMAIN=polimedia.pblworkspace.com`, `SESSION_SECURE_COOKIE=true` | yes (TLS) | |
| `DB_*` | yes | MySQL (or compatible) |
| `QUEUE_CONNECTION` | recommend `database` for prod | default `.env.example` is `sync` |
| `BROADCAST_CONNECTION=log` | leave alone | no Pusher/Reverb wired up |
| `MAIL_*` | yes | swap from Mailhog to real SMTP / relay (see Mail) |
| `JITSI_DOMAIN`, `JITSI_JWT_APP_ID`, `JITSI_JWT_APP_SECRET` | yes for conferences | `JitsiTokenService::mint()` throws if any is missing |
| `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` | yes for push | `php artisan webpush:vapid` to generate |
| `PISTON_URL`, `PISTON_TIMEOUT` | yes for exercise Run | default `http://piston:2000/api/v2` (uses bundled container) |

---

## Provisioning: Jitsi (self-hosted)

Conference rooms run on a self-hosted Jitsi at `meet.polimedia.pblworkspace.com`, behind Caddy on the same VM. Full step-by-step (DNS, firewall, Caddy, `docker-jitsi-meet` install, JWT secret generation, end-to-end verification, rollback) is in [ops/jitsi-self-host.md](ops/jitsi-self-host.md).

After Jitsi is running and you have the JWT secret from `~/jitsi-meet/.env`:

1. `JITSI_DOMAIN=meet.polimedia.pblworkspace.com`
2. `JITSI_JWT_APP_ID=<JWT_APP_ID from Jitsi .env>` (e.g. `pjbl`)
3. `JITSI_JWT_APP_SECRET=<JWT_APP_SECRET from Jitsi .env>` — 32-byte hex via `openssl rand -hex 32`. Never commit.
4. `php artisan config:clear && php artisan config:cache`

`App\Services\JitsiTokenService::mint()` throws `RuntimeException('Jitsi JWT credentials not configured…')` if `JITSI_JWT_APP_ID` or `JITSI_JWT_APP_SECRET` is missing — surface that error in staging before prod or every conference room view will 500.

JWT lifetime is 2 hours, signed HS256. Claims: `aud=iss=JITSI_JWT_APP_ID`, `sub=JITSI_DOMAIN`, `room=<conference.room_name>`, moderator flag inside `context.user.moderator` as a string (`'true'`/`'false'`).

---

## Provisioning: WebPush / VAPID

```bash
php artisan webpush:vapid
```

Copy both keys into `.env`:

```env
VAPID_PUBLIC_KEY=BB….
VAPID_PRIVATE_KEY=…
VAPID_SUBJECT=mailto:admin@pblworkspace.com
```

**Keys must be stable**. Rotating them invalidates every row in `push_subscriptions` — every browser re-subscribes on next page load anyway (the layout's inline service-worker bootstrap calls `pushManager.subscribe()` automatically), but in-flight notifications between rotation and re-subscribe will fail to deliver.

`VAPID_PUBLIC_KEY` is read directly from `env()` inside `layouts/app.blade.php` — calling `php artisan config:cache` does **not** bake it into the cache for that template. Don't rely on `config:clear` to surface a missing key; it'll just be empty.

---

## Queue worker

`composer dev` includes `php artisan queue:listen --tries=1 --timeout=0` for local — fine because `QUEUE_CONNECTION=sync` by default (notifications never enqueue).

**In production**, set `QUEUE_CONNECTION=database` and run a supervised worker:

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

`reload supervisorctl` then `supervisorctl restart pjbl-queue`. Every notification in `app/Notifications/` implements `ShouldQueue`, so a stopped worker means **no notifications get delivered** under `database` driver. If you want sync-only delivery, leave `QUEUE_CONNECTION=sync` and skip the worker entirely — the cost is slow request handlers when a single dispatch fans out to many recipients (e.g. an announcement to all mahasiswa).

---

## Mail

For production, switch from MailHog to real SMTP or a third-party relay (GCP blocks port 25, so direct outbound SMTP from the VM does not work — use a service like Resend, Brevo, Postmark, Mailgun, or SES):

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

Mail-channel notifications in this app: `OtpVerificationNotification`, `ResetPasswordNotification`. Both are `ShouldQueue`.

---

## File storage

`FILESYSTEM_DISK=local` by default. Uploaded content (material files, submissions, announcement attachments, course banners) is stored on the `public` disk under:

- `storage/app/public/materials/…`
- `storage/app/public/submissions/…`
- `storage/app/public/announcements/…`

`php artisan storage:link` creates `public/storage` → `storage/app/public`. Re-run after any deploy that wipes `public/`.

---

## Backups

The repo doesn't ship a backup story. Minimal recommendation on the VM:

```bash
# Daily MySQL dump
docker compose exec -T db mysqldump -uroot -p"$DB_ROOT_PASSWORD" pjbl > /backups/pjbl_$(date +%F).sql

# Sync to GCS
gsutil rsync -r /backups gs://pjbl-backups/
```

Also snapshot the VM disk through GCP Console after the Jitsi cutover (see [ops/jitsi-self-host.md](ops/jitsi-self-host.md) Step 5).

---

## Rollback

- App revert: `git revert <sha>` on the deploy branch, then `composer optimize` and bounce PHP-FPM + queue worker.
- Database: restore from the latest dump if a migration corrupts data. **Never** `php artisan migrate:rollback` blind in prod — read the `down()` method first.
- Jitsi rollback to JaaS is no longer possible without re-introducing the removed code (JaaS support, the RSA key file at `storage/app/private/jaas-private-key.pk`, and the old env keys are gone — see the cleanup section of [ops/jitsi-self-host.md](ops/jitsi-self-host.md)). If you need to revert, bring up a managed conferencing provider and reintroduce its driver in `JitsiTokenService` (or a sibling service).

---

## Logging

`LOG_CHANNEL=stack`, `LOG_STACK=single`, `LOG_LEVEL=debug` by default. In production, ratchet to `LOG_LEVEL=info` or `warning` and consider switching to `daily` for rotation:

```env
LOG_STACK=daily
LOG_LEVEL=info
```

Pail (`php artisan pail`) is included in `composer dev` for local log tailing.
