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

## Provisioning: Jitsi JaaS

Conference rooms require a valid JaaS account at [8x8.vc](https://jaas.8x8.vc).

1. Create a JaaS app in the dashboard
2. Copy the `APP_ID` (e.g. `vpaas-magic-cookie-abc123`) → `JITSI_APP_ID`
3. Generate an API key in the dashboard → copy the `KID` → `JITSI_KID`
4. Download the RS256 private key file
5. Place the private key at the path in `JITSI_PRIVATE_KEY_PATH` (default: `storage/app/private/jaas-private-key.pk`)

The `storage/app/private/` directory must exist and be unreadable by the web server. The key file is never committed to git.

`JaasTokenService::mint()` throws a `RuntimeException` if any of the four vars are missing or the key file can't be read. Conference room views will 500 — surface this error in staging before prod.

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
