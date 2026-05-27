# Deployment

## Docker Services

| Service | Image | Port | Purpose |
|---|---|---|---|
| `app` | Custom PHP-FPM (Dockerfile) | — | PHP application (FastCGI) |
| `web` | `fholzer/nginx-brotli:v1.25.3` | `8000:80` | Nginx reverse proxy |
| `db` | `mysql:8.0` | — (internal) | MySQL 8 database |
| `phpmyadmin` | `phpmyadmin:latest` | `8081:80` | DB GUI |
| `mailhog` | `mailhog/mailhog` | `8025:8025` | Mail catcher |

The `app` and `db` containers share `pjbl-network` bridge. `web` proxies to `app:9000` (FastCGI). Only `web`, `phpmyadmin`, and `mailhog` expose host ports.

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

---

## Nginx Config Highlights

`docker/nginx/conf.d/app.conf`:

- Brotli + Gzip compression enabled for text assets
- Cache-Control `immutable, max-age=31536000` (1 year) on Vite-fingerprinted assets (`/build/`)
- FastCGI passes to `app:9000`
- `try_files $uri $uri/ /index.php?$query_string` for Laravel routing

---

## Production Checklist

```bash
# 1. Set env
APP_ENV=production
APP_DEBUG=false

# 2. Fill required secrets (see below)

# 3. Run migrations
php artisan migrate --force

# 4. Cache everything
composer optimize   # config:cache + route:cache + view:cache + event:cache

# 5. Build assets
npm run build

# 6. Start queue worker (separate process, not inside composer dev)
php artisan queue:listen --tries=3

# 7. Set correct file permissions on storage/
chmod -R 775 storage bootstrap/cache
```

`composer optimize` must run after every deploy, not just once. Config changes don't take effect until the cache is cleared.

---

## Provisioning: Jitsi (self-hosted)

Conference rooms run on a self-hosted Jitsi instance at `meet.polimedia.pblworkspace.com` (same GCP VM as the app, behind Caddy reverse proxy). See the ops handoff in `docs/ops/jitsi-self-host.md` for the full VM setup procedure.

After Jitsi is up and you have the values from the Jitsi `.env`:

1. Set `JITSI_DOMAIN` to the public hostname (e.g. `meet.polimedia.pblworkspace.com`).
2. Set `JITSI_JWT_APP_ID` to the same value as the Jitsi server's `JWT_APP_ID` (e.g. `pjbl`).
3. Set `JITSI_JWT_APP_SECRET` to the same value as the Jitsi server's `JWT_APP_SECRET` (32-byte hex string). Never commit this to git.

`JitsiTokenService::mint()` throws a `RuntimeException` if `JITSI_JWT_APP_ID` or `JITSI_JWT_APP_SECRET` is missing. Conference room views will 500 — surface this error in staging before prod.

---

## Provisioning: WebPush / VAPID

```bash
php artisan webpush:vapid
```

Copy both keys into `.env`:

```env
VAPID_PUBLIC_KEY=<output>
VAPID_PRIVATE_KEY=<output>
VAPID_SUBJECT=mailto:your@email.com
```

VAPID keys must be stable — rotating them invalidates all existing push subscriptions in `push_subscriptions`. Mahasiswa will need to re-subscribe.

---

## Queue Worker

`composer dev` already runs `php artisan queue:listen --tries=1 --timeout=0` as one of its concurrent processes — fine for local.

In production, run the queue worker as a supervised background process (Supervisor, systemd, etc.):

```ini
# /etc/supervisor/conf.d/pjbl-queue.conf
[program:pjbl-queue]
command=php /var/www/artisan queue:listen --tries=3
autostart=true
autorestart=true
```

All notifications dispatch synchronously by default (no `ShouldQueue`). Only `ResetPasswordNotification` queues. If you add `ShouldQueue` to other notifications, the queue worker becomes mandatory for those notifications to send.

---

## Mail

For production, switch `MAIL_MAILER=log` to `smtp` and configure your provider:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@example.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
```
