# Getting Started

## Prerequisites

- PHP 8.2+ with extensions: `pdo_sqlite` (dev), `pdo_mysql` (Docker/prod), `gd`, `zip`, `bcmath`
- Node 20+
- Composer 2
- Docker + Docker Compose (optional — for the containerized path)

---

## Native Dev (preferred)

```bash
git clone <repo> pjbl && cd pjbl
composer setup    # install → .env → key:generate → migrate → npm install → build → optimize
composer dev      # concurrent: artisan serve + queue:listen + pail + vite
```

`composer setup` copies `.env.example` → `.env` if no `.env` exists. SQLite is the default database for local dev — no MySQL needed.

App: `http://localhost:8000` | Vite HMR: Vite proxies through artisan serve.

---

## Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Then open `http://localhost:8000`. PHPMyAdmin at `http://localhost:8081`, Mailhog at `http://localhost:8025`.

For Docker you'll need a MySQL `.env` — override the SQLite defaults:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pjbl
DB_USERNAME=pjbl
DB_PASSWORD=password
```

Mail is pre-configured to hit Mailhog (`MAIL_HOST=mailhog`, `MAIL_PORT=1025`) in the Docker env — update these manually if you're using native dev.

---

## Environment Variables

All vars live in `.env.example`. Required ones that are **empty by default** must be filled before the related feature works.

### App

| Variable | Default | Notes |
|---|---|---|
| `APP_NAME` | `PBL Workspace` | Shown in UI and emails |
| `APP_ENV` | `local` | Set to `production` when deploying |
| `APP_KEY` | _(generated)_ | Auto-filled by `key:generate` |
| `APP_DEBUG` | `true` | Must be `false` in production |
| `APP_URL` | `http://localhost` | Full URL including scheme |

### Database

| Variable | Default | Notes |
|---|---|---|
| `DB_CONNECTION` | `sqlite` | Switch to `mysql` for Docker/prod |
| `DB_HOST` | — | MySQL host (Docker: `db`) |
| `DB_PORT` | — | MySQL port (usually `3306`) |
| `DB_DATABASE` | — | MySQL database name |
| `DB_USERNAME` | — | MySQL user |
| `DB_PASSWORD` | — | MySQL password |

### Session / Cache / Queue

| Variable | Default | Notes |
|---|---|---|
| `SESSION_DRIVER` | `database` | `file` is faster for single-server local dev |
| `CACHE_STORE` | `database` | Switch to `redis` for prod if needed |
| `QUEUE_CONNECTION` | `database` | Queue worker runs via `queue:listen` in `composer dev` |
| `BROADCAST_CONNECTION` | `log` | No real broadcasting; don't change without adding a driver |

### Mail

| Variable | Default | Notes |
|---|---|---|
| `MAIL_MAILER` | `log` | Writes to `storage/logs` locally — switch to `smtp` for prod |
| `MAIL_HOST` | `127.0.0.1` | Docker: `mailhog`, prod: your SMTP host |
| `MAIL_PORT` | `2525` | Mailhog: `1025`, SMTP: `587` |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Change before prod |

### Jitsi (self-hosted, required for conference rooms)

| Variable | Default | Notes |
|---|---|---|
| `JITSI_DOMAIN` | `meet.polimedia.pblworkspace.com` | Public hostname of the self-hosted Jitsi |
| `JITSI_JWT_APP_ID` | _(empty)_ | Must match Jitsi server's `JWT_APP_ID` |
| `JITSI_JWT_APP_SECRET` | _(empty)_ | Must match Jitsi server's `JWT_APP_SECRET` (HS256) |

See [deployment.md](deployment.md) for provisioning steps.

### WebPush / VAPID (required for push notifications)

| Variable | Default | Notes |
|---|---|---|
| `VAPID_PUBLIC_KEY` | _(empty)_ | Generate with `php artisan webpush:vapid` |
| `VAPID_PRIVATE_KEY` | _(empty)_ | Same command |
| `VAPID_SUBJECT` | `mailto:admin@example.com` | Contact email for push service |

### Code Execution Proxy

| Variable | Default | Notes |
|---|---|---|
| `PISTON_URL` | `https://emkc.org/api/v2/piston` | Public Piston API; replace with self-hosted if needed |
| `PISTON_TIMEOUT` | `10` | Seconds before proxy gives up |

---

## Default Seeded Accounts

After `migrate --seed`, these accounts exist (password: `password` for all):

| Role | Email |
|---|---|
| Admin | `admin@pjbl.test` |
| Dosen (primary) | `dosen@pjbl.test` |
| Dosen | `budi.dosen@pjbl.test` |
| Dosen | `siti.dosen@pjbl.test` |
| Mahasiswa (primary) | `mahasiswa@pjbl.test` |
| Mahasiswa | `ahmad.mhs@pjbl.test` |

---

## Daily Commands

```bash
composer dev          # start dev server (serve + queue + pail + vite)
composer test         # run Pest suite (clears config cache first)
vendor/bin/pint       # fix PHP code style
npm run build         # production asset build
npm run audit:contrast  # check color contrast ratios
```
