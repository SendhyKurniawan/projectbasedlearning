import { execSync } from 'node:child_process';

/**
 * Reset the database to DuskSeeder state. Expensive — call from globalSetup
 * or sparingly between test files. Uses artisan so it honors the real DB
 * connection (.env) rather than reaching in with a client library.
 */
export function reseedDatabase(): void {
  try {
    execSync('php artisan migrate:fresh --seeder=DuskSeeder --no-interaction', {
      cwd: process.cwd(),
      stdio: 'inherit',
      env: { ...process.env },
      timeout: 120_000,
    });
  } catch (err) {
    console.error('[reset] migrate:fresh failed. Is the DB reachable?');
    throw err;
  }
}

/**
 * Lighter reset: only re-run the seeder without dropping tables.
 * Requires that the seeder truncate-then-insert (DuskSeeder does).
 */
export function reseedOnly(): void {
  execSync('php artisan db:seed --class=DuskSeeder --no-interaction', {
    cwd: process.cwd(),
    stdio: 'inherit',
    env: { ...process.env },
    timeout: 60_000,
  });
}
