#!/usr/bin/env bash
set -e

cd /var/www

echo "[entrypoint] Bootstrapping PBL Workspace..."

# ---------------------------------------------------------------------------
# 1. .env — copy from example on first boot so APP_KEY can be generated.
# ---------------------------------------------------------------------------
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "[entrypoint] .env missing — copying .env.example"
        cp .env.example .env
    else
        echo "[entrypoint] WARNING: no .env or .env.example found"
    fi
fi

# ---------------------------------------------------------------------------
# 2. vendor/ — restore if the host bind-mount nuked it (common on Windows).
# ---------------------------------------------------------------------------
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ missing — running composer install"
    composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader
fi

# ---------------------------------------------------------------------------
# 3. Writable directories (storage & bootstrap/cache).
# ---------------------------------------------------------------------------
mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# ---------------------------------------------------------------------------
# 4. APP_KEY — generate once if blank.
# ---------------------------------------------------------------------------
if ! grep -qE '^APP_KEY=base64:' .env; then
    echo "[entrypoint] APP_KEY missing — generating"
    php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# 5. Wait for the database to accept connections before migrating.
# ---------------------------------------------------------------------------
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 60); do
        if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
            echo "[entrypoint] Database is up."
            break
        fi
        sleep 2
        if [ "$i" -eq 60 ]; then
            echo "[entrypoint] ERROR: database not reachable after 120s"
            exit 1
        fi
    done
fi

# ---------------------------------------------------------------------------
# 6. Storage symlink (public/storage -> storage/app/public).
# ---------------------------------------------------------------------------
if [ ! -L public/storage ]; then
    echo "[entrypoint] Creating storage symlink"
    php artisan storage:link || true
fi

# ---------------------------------------------------------------------------
# 7. Migrations. Seed when the database has no users (covers fresh clone and
#    `docker compose down -v` resets).
# ---------------------------------------------------------------------------
echo "[entrypoint] Running migrations..."
php artisan migrate --force

if [ "${DB_SEED:-true}" = "true" ]; then
    USER_COUNT="$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1 | tr -dc '0-9')"
    if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
        echo "[entrypoint] Empty users table detected — seeding database"
        php artisan db:seed --force || echo "[entrypoint] Seeder failed (continuing anyway)"
    else
        echo "[entrypoint] Database already populated (users=$USER_COUNT) — skipping seed"
    fi
fi

# ---------------------------------------------------------------------------
# 8. Discover packages + clear stale caches generated against old paths/env.
# ---------------------------------------------------------------------------
php artisan package:discover --ansi || true
php artisan optimize:clear || true

echo "[entrypoint] Ready. Starting: $*"
exec "$@"
