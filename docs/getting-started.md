# Getting Started

## Prerequisites

- **PHP 8.2+** with extensions: `pdo_mysql`, `gd`, `zip`, `bcmath`, `intl`, `mbstring`, `xml`, `pdo_sqlite` (for Pest's `RefreshDatabase` tests)
- **Composer 2**
- **Node 20+** with NPM 10+
- **Docker** + Docker Compose (for the containerized path or local Piston / Mailhog / MySQL)
- **Git**

---

## Docker path (matches production)

The Docker path uses MySQL 8, Mailhog, and a bundled Piston code-execution sandbox — the same composition that runs in production behind Caddy.

```bash
git clone <repo> pjbl && cd pjbl
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Then:

- App: `http://localhost:8000`
- phpMyAdmin: `http://localhost:8081`
- MailHog UI: `http://localhost:8025`

The `app` container's entrypoint copies `.env.example` → `.env` on first boot (if `.env` doesn't already exist) and waits for the `db` container's healthcheck before starting PHP-FPM.

> The host-side port for the app is `127.0.0.1:8000` (bound to localhost only). In production, Caddy on the host proxies HTTPS:443 → `127.0.0.1:8000`. Locally you hit `http://localhost:8000` directly.

### Vite during dev

Run Vite inside the container or natively on the host. Inside:

```bash
docker compose exec app npm install
docker compose exec app npm run dev
```

If you add new Vite entry points later, **remove the `app_build` volume before re-deploying** — it shadows new builds. See [deployment.md](deployment.md#vite-assets-gotcha).

---

## Native path (no Docker)

```bash
git clone <repo> pjbl && cd pjbl
composer setup        # install → .env → key:generate → migrate → npm install → npm run build → composer optimize
composer dev          # concurrent: php artisan serve + queue:listen + pail + vite
```

`composer setup` copies `.env.example` to `.env` if missing, generates `APP_KEY`, runs migrations (force), installs node modules, builds production assets, then caches config/routes/views/events.

`composer dev` runs four processes via `concurrently`:

| Name | Command |
|---|---|
| `server` | `php artisan serve` (default `http://127.0.0.1:8000`) |
| `queue` | `php artisan queue:listen --tries=1 --timeout=0` |
| `logs` | `php artisan pail --timeout=0` (real-time log tail) |
| `vite` | `npm run dev` |

For native dev you'll want to either run MySQL locally or override the database vars to use SQLite. Quick SQLite setup:

```bash
touch database/database.sqlite
# .env overrides:
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
# (or leave blank — Laravel resolves a relative `database/database.sqlite` if env value omitted)
```

The default `.env.example` is wired for MySQL with Docker hostnames (`DB_HOST=db`, `DB_PORT=3306`). When running natively against a host MySQL, change `DB_HOST=127.0.0.1`.

---

## Environment variables

Full reference. Values shown are the `.env.example` defaults.

### App

| Variable | Default | Notes |
|---|---|---|
| `APP_NAME` | `PBL Workspace` | shown in UI, email subjects, push titles |
| `APP_ENV` | `local` | switch to `production` when deploying |
| `APP_KEY` | _(empty, run `key:generate`)_ | |
| `APP_DEBUG` | `true` | **must be `false`** in production |
| `APP_URL` | `http://localhost:8000` | absolute URL incl. scheme; prod example `https://polimedia.pblworkspace.com` |
| `APP_LOCALE` | `id` | UI strings are Indonesian; date helpers use Carbon's locale |
| `APP_FALLBACK_LOCALE` | `en` | |
| `APP_FAKER_LOCALE` | `en_US` | factories |
| `APP_MAINTENANCE_DRIVER` | `file` | |
| `BCRYPT_ROUNDS` | `10` | |

### Logging

| Variable | Default |
|---|---|
| `LOG_CHANNEL` | `stack` |
| `LOG_STACK` | `single` |
| `LOG_DEPRECATIONS_CHANNEL` | `null` |
| `LOG_LEVEL` | `debug` |

### Database

| Variable | Default | Notes |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Default is MySQL to match Docker. Use `sqlite` for native + zero-setup dev. |
| `DB_HOST` | `db` | Docker service hostname; `127.0.0.1` for native |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `pjbl` | |
| `DB_USERNAME` | `pjbl` | |
| `DB_PASSWORD` | `password` | |

### Session / Cache / Queue / Broadcast / Filesystem

| Variable | Default | Notes |
|---|---|---|
| `SESSION_DRIVER` | `database` | `file` is faster for single-server local; `redis` for multi-server |
| `SESSION_LIFETIME` | `120` (minutes) | |
| `SESSION_ENCRYPT` | `false` | |
| `SESSION_PATH` | `/` | |
| `SESSION_DOMAIN` | `null` | production: set to your apex domain (e.g. `polimedia.pblworkspace.com`) |
| `CACHE_STORE` | `database` | switch to `redis` in prod if Redis is available |
| `QUEUE_CONNECTION` | `sync` | dispatches inline; switch to `database` in prod and run a worker |
| `BROADCAST_CONNECTION` | `log` | no Pusher/Reverb. Do not call `broadcast()` without setting up a driver first |
| `FILESYSTEM_DISK` | `local` | uploaded content via the `public` disk (`storage/app/public`) — `php artisan storage:link` once |

### Mail

| Variable | Default | Notes |
|---|---|---|
| `MAIL_MAILER` | `smtp` | |
| `MAIL_HOST` | `mailhog` | Docker hostname for the Mailhog container; native: `127.0.0.1` |
| `MAIL_PORT` | `1025` | Mailhog SMTP port |
| `MAIL_USERNAME` | `null` | |
| `MAIL_PASSWORD` | `null` | |
| `MAIL_ENCRYPTION` | `null` | |
| `MAIL_FROM_ADDRESS` | `noreply@pbl.test` | |
| `MAIL_FROM_NAME` | `${APP_NAME}` | |

GCP blocks outbound port 25; for production use a relay (Resend / Brevo / Postmark / Mailgun / SES).

### AWS (optional, only if you switch `FILESYSTEM_DISK` to S3)

| Variable | Default |
|---|---|
| `AWS_ACCESS_KEY_ID` | _(empty)_ |
| `AWS_SECRET_ACCESS_KEY` | _(empty)_ |
| `AWS_DEFAULT_REGION` | `us-east-1` |
| `AWS_BUCKET` | _(empty)_ |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` |

### Jitsi (self-hosted)

| Variable | Default | Notes |
|---|---|---|
| `JITSI_DOMAIN` | `meet.polimedia.pblworkspace.com` | public hostname; the front-end builds room URLs as `https://{domain}/{room_name}?jwt=…` |
| `JITSI_JWT_APP_ID` | _(empty)_ | must match the Jitsi server's `JWT_APP_ID` (e.g. `pjbl`). Required — `JitsiTokenService::mint()` throws without it. |
| `JITSI_JWT_APP_SECRET` | _(empty)_ | must match the Jitsi server's `JWT_APP_SECRET`. 32-byte hex; never commit. |

For local-only conference development without a real Jitsi server, set `JITSI_DOMAIN` to anything ("meet.local"), and any non-empty `JITSI_JWT_APP_ID` + secret. The minted token will be technically valid; opening the meeting tab will just 404 unless you actually point at a server.

### Code execution (Piston)

| Variable | Default | Notes |
|---|---|---|
| `PISTON_URL` | `http://piston:2000/api/v2` | points at the bundled `piston` container in Docker; for native dev, use `https://emkc.org/api/v2/piston` |
| `PISTON_TIMEOUT` | `10` | seconds before the proxy returns `502 Execution service unavailable.` |

Allowed languages (`config/code_execution.php`): `java`, `php`, `csharp` (Piston runs C# as `csharp.net`).

### WebPush (VAPID)

| Variable | Default | Notes |
|---|---|---|
| `VAPID_PUBLIC_KEY` | _(empty)_ | required for push notifications. Generate with `php artisan webpush:vapid`. |
| `VAPID_PRIVATE_KEY` | _(empty)_ | same command. |
| `VAPID_SUBJECT` | `mailto:admin@example.com` | contact email for the push service. |

If `VAPID_PUBLIC_KEY` is empty, the layout silently skips subscribing the browser — push just doesn't work until you fill it.

---

## Default seeded accounts

`DatabaseSeeder` runs (in order): `AcademicYearSeeder`, `SemesterSeeder`, `DepartmentSeeder`, `StudyProgramSeeder`, `StudentClassSeeder`, `UserSeeder`, `CourseSeeder`, `DummyDataSeeder`, `AssignmentSeeder`.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@pjbl.test` | `password` |
| Dosen | `dosen@pjbl.test` | `password` |
| Dosen | `budi.dosen@pjbl.test` | `password` |
| Dosen | `siti.dosen@pjbl.test` | `password` |
| Mahasiswa | `mahasiswa@pjbl.test` | `password` (`student_class_id=1`) |
| Mahasiswa | `ahmad.mhs@pjbl.test`, `dewi.mhs@…`, `cahya.mhs@…`, `rina.mhs@…`, `fajar.mhs@…` | `password` (random `student_class_id ∈ {1,2}`) |

Admin login is at `/admin/login` (separate Breeze controller); the other roles log in at `/login`.

---

## Daily commands

```bash
composer dev          # start dev server (serve + queue + pail + vite)
composer test         # run Pest suite (clears config cache first)
vendor/bin/pint       # fix PHP code style
npm run build         # production asset build
npm run dev           # vite only (HMR)
npm run audit:contrast  # check WCAG contrast on the design token palette

php artisan migrate:fresh --seed     # reset DB + reseed
php artisan storage:link             # link public storage to storage/app/public
php artisan webpush:vapid            # generate VAPID keys (write into .env)
```

Docker variants:

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan tinker
docker compose logs -f app
```

---

## First-time troubleshooting

- **`SQLSTATE[HY000] [2002]`** — MySQL not up yet or hostname wrong. Inside docker, host should be `db`; outside, use `127.0.0.1` with port forwarded.
- **`Vite manifest not found at: public/build/manifest.json`** — Run `npm run build` (prod) or `npm run dev` (HMR).
- **Push notifications never arrive** — `VAPID_PUBLIC_KEY` empty, `Notification.requestPermission()` blocked, or the service worker (`/sw.js`) failed to register. Check the browser console.
- **Conference page 500s** — `JITSI_JWT_APP_ID` or `JITSI_JWT_APP_SECRET` missing. `JitsiTokenService::mint()` throws `RuntimeException` before rendering.
- **Exercise "Run" returns 502** — Piston container not running, or `PISTON_URL` unreachable. Inside docker: `docker compose ps piston`.
- **Login OK but stuck on login screen** — Account has `is_active=false`. Mahasiswa with unverified OTP get redirected to `/verify-otp`; dosen waiting admin approval see the "Akun belum diaktifkan" error.

See [auth-roles.md](auth-roles.md) for the registration/OTP/admin-approval flow.
